<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Uri;
use Grav\Common\Utils;
use RocketTheme\Toolbox\File\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Class StarRatingsPlugin
 * @package Grav\Plugin
 */
class StarRatingsPlugin extends Plugin
{
    protected $callback;
    protected $total_stars;
    protected $only_full_stars;

    protected $stars_data_path;
    protected $ips_data_path;

    protected $vote_data;

    protected $stars_cache_id;
    protected $ips_cache_id;

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onPagesInitialized'   => ['onPagesInitialized', 0],
        ];
    }

    /**
     * Add templates directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/admin/templates';
    }


    public function onPagesInitialized()
    {
        $uri = $this->grav['uri'];
        $cache = $this->grav['cache'];

        $this->callback = $this->config->get('plugins.star-ratings.callback');
        $this->total_stars = $this->config->get('plugins.star-ratings.total_stars');
        $this->only_full_stars = $this->config->get('plugins.star-ratings.only_full_stars');

        $data_path = $this->grav['locator']->findResource('user://data', true) . '/star-ratings/';
        $this->stars_data_path = $data_path . 'stars.yaml';
        $this->ips_data_path =$data_path . 'ips.yaml';

        $this->stars_cache_id = md5('stars-vote-data'.$cache->getKey());
        $this->ips_cache_id = md5('stars-ip-data'.$cache->getKey());

        if ($this->callback != $uri->path()) {
            return;
        }

        $result = $this->addVote();

        echo json_encode(['status' => $result[0], 'message' => $result[1]]);
        exit();
    }

    public function addVote()
    {
        $nonce = $this->grav['uri']->param('nonce');
        if (!Utils::verifyNonce($nonce, 'star-ratings')) {
            return [false, 'Invalid security nonce'];
        }

        $star_rating = filter_input(INPUT_POST, 'rating', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $id          = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);

        // ensure both values are sent
        if (is_null($star_rating) || is_null($id)) {
            return [false, 'missing either id or rating'];
        }

        // check for duplicate vote if configured
        if ($this->config->get('plugins.star-ratings.deny_repeats')) {
            if (!$this->validateIp($id)) {
                return [false, 'This IP has already voted'];
            }
        }

        // sanity checks for star ratings
        if ($star_rating < 0) {
            $star_rating = 0;
        } elseif ($star_rating > $this->total_stars ) {
            $star_rating = $this->total_stars;
        }

        // get an int if you pass a float and you shouldn't be
        if (is_float($star_rating) && $this->only_full_stars) {
            $star_rating = ceil($star_rating);
        }

        $vote_data = $this->getVoteData();

        if (array_key_exists($id, $vote_data)) {
            $rating = $vote_data[$id];
            $rating['count']++;
            array_push($rating['votes'], $star_rating);
            $rating['score'] = array_sum($rating['votes']) / $rating['count'];

        } else {
            $rating['count'] = 1;
            $rating['votes'] = [$star_rating];
            $rating['score'] = $star_rating;
        }

        $this->saveVoteData($id, $rating);

        return [true, 'Your vote has been added!'];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            $this->enable([
                'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0]
            ]);
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onTwigInitialized' => ['onTwigInitialized', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
        ]);

        $this->getVoteData();
    }

    public function onTwigInitialized()
    {
        $this->grav['twig']->twig()->addFunction(
            new \Twig_SimpleFunction('stars', [$this, 'generateStars'])
        );
    }

    public function onTwigSiteVariables()
    {
        if ($this->config->get('plugins.star-ratings.built_in_css')) {
            $this->grav['assets']
                ->addCss('plugin://star-ratings/assets/star-ratings.css');
        }

        $this->grav['assets']
            ->add('jquery', 101)
            ->addJs('plugin://star-ratings/assets/jquery.star-rating-svg.min.js')
            ->addJs('plugin://star-ratings/assets/star-ratings.js');


    }

    public function generateStars($id=null, $options = [])
    {
        if ($id === null) {
            return '<i>ERROR: no id provided to <code>stars()</code> twig function</i>';
        }

        $total_stars = $this->config->get('plugins.star-ratings.total_stars');

        $data = [
            'id' => $id,
            'uri' => Uri::addNonce($this->grav['base_url'] . $this->config->get('plugins.star-ratings.callback') . '.json','star-ratings'),
            'options' => [
                'totalStars' => $this->config->get('plugins.star-ratings.total_stars'),
                'initialRating' => $this->getStars($id),
                'starSize' => $this->config->get('plugins.star-ratings.star_size'),
                'useFullStars' => $this->config->get('plugins.star-ratings.use_full_stars'),
                'emptyColor' => $this->config->get('plugins.star-ratings.empty_color'),
                'hoverColor' => $this->config->get('plugins.star-ratings.hover_color'),
                'activeColor' => $this->config->get('plugins.star-ratings.active_color'),
                'useGradient' => $this->config->get('plugins.star-ratings.use_gradient'),
                'starGradient' => [
                    'start' => $this->config->get('plugins.star-ratings.star_gradient_start'),
                    'end' => $this->config->get('plugins.star-ratings.star_gradient_end')
                ],
                'readOnly' => $this->config->get('plugins.star-ratings.readonly'),
                'disableAfterRate' => $this->config->get('plugins.star-ratings.disable_after_rate'),
                'strokeWidth' => $this->config->get('plugins.star-ratings.stroke_width'),
                'strokeColor' => $this->config->get('plugins.star-ratings.stroke_color')
            ]
        ];

        $data['options'] = array_replace_recursive($data['options'], $options);

        $data = htmlspecialchars(json_encode($data, ENT_QUOTES));

        return '<div class="star-rating-container" data-star-rating="'.$data.'"></div>';
    }

    private function getVoteData()
    {
        if (empty($this->vote_data)) {
            $cache = $this->grav['cache'];
            $vote_data = $cache->fetch($this->stars_cache_id);

            if ($vote_data === false) {
                $fileInstance = File::instance($this->stars_data_path);

                if (!$fileInstance->content()) {
                    $vote_data = [];
                } else {
                    $vote_data = Yaml::parse($fileInstance->content());
                }
                // store data in plugin
                $this->vote_data = $vote_data;

                // store data in cache
                $cache->save($this->stars_cache_id, $this->vote_data);
            }
        }
        return $this->vote_data;
    }

    private function saveVoteData($id = null, $data = null)
    {
        if ($id != null && $data !=null) {
            $this->vote_data[$id] = $data;
        }

        // update data in cache
        $this->grav['cache']->save($this->stars_cache_id, $this->vote_data);

        // save in file
        $fileInstance = File::instance($this->stars_data_path);
        $yaml = Yaml::dump($this->vote_data);
        $fileInstance->content($yaml);
        $fileInstance->save();
    }

    private function getStars($id)
    {
        $vote_data = $this->getVoteData();
        if (array_key_exists($id, $vote_data)) {
            return $vote_data[$id]['score'];
        } else {
            return 0;
        }
    }

    private function validateIp($id)
    {
        $user_ip = $this->grav['uri']->ip();
        $fileInstance = File::instance($this->ips_data_path);

        if (!$fileInstance->content()) {
            $ip_data = [];
        } else {
            $ip_data = Yaml::parse($fileInstance->content());
        }

        if (array_key_exists($user_ip, $ip_data)) {
            $user_ip_data = $ip_data[$user_ip];
            if (in_array($id, $user_ip_data)) {
                return false;
            }  else {
                array_push($user_ip_data, $id);
            }
        } else {
            $user_ip_data = [$id];
        }

        $ip_data[$user_ip] = $user_ip_data;

        $yaml = Yaml::dump($ip_data);
        $fileInstance->content($yaml);
        $fileInstance->save();

        return true;

    }

}
