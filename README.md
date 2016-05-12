# Star Ratings Plugin

The **Star Ratings** Plugin is for [Grav CMS](http://github.com/getgrav/grav).  This README.md file should be modified to describe the features, installation, configuration, and general usage of this plugin.

## Description

Simple but Powerful Star Ratings plugin for all your needs. All you need is to pass it some unique id. 

This allows you to easily add star ratings or a page URL, an image name, comments, pretty much anything!  This plugin uses SVG-based stars so they are fully customizable in color and size, as well as stroke, and you can also configure the number of stars (default is 5).  You can fully configure the behavior too, including the ability to vote in half stars or display the number of votes.

Star votes are fully cached utilizing Grav's caching mechanism, so performance is not a problem.  And using stars could not be simpler!

## Installation

First ensure you are running the latest **Grav 1.0.10 or later**. To force an upgrade of Grav run the following:

```
$ bin/gpm selfupgrade -f
```

The Star Ratings can be installed via the Admin plugin, but it can also be installed from the CLI with the following command:

```
$ bin/gpm install star-ratings
```

## Configuration

The simplest way to configure the plugin is via the Admin Plugin.  Simply navigate to **Plugins** then select **Star Ratings** to access the configuration options.

Alternatively, you can configure options manually by simply copying the `user/plugins/star-ratings/star-ratings.yaml` into `user/config/plugins/star-ratings.yaml` and making your modifications.

```
enabled: true
callback: '/star-ratings'
built_in_css: true
unique_ip_check: true
initial_stars: 0
total_stars: 5
star_size: 25
use_full_stars: false
empty_color: '#e3e3e3'
hover_color: '#1bb3e9'
active_color: '#ffd700'
use_gradient: true
star_gradient_start: '#fef7cd'
star_gradient_end: '#ffcc00'
readonly: false
disable_after_rate: true
stroke_width: 0
stroke_color: '#999999'
show_count: false
```

## Page Overrides

You can override any of these settings (except `enabled`) via page frontmatter overrides.  For example if you have a set of defaults, and you want to override the size and stroke width of the stars on a particular page, you can do so with the following YAML in the page's frontmatter:

```
star-ratings:
  star_size: 45
  stroke_width: 5
```

These values are merged with the defaults so you don't need to include everything, only what you want to override.

## Usage

To use this plugin, you simplest way to output the stars is to use the Twig function and pass it a **Unique ID**:

```
{{ stars(232) }}
```

This is using the hard-coded value of 232 as the unique ID.  You can however, use a dynamic value such as:

```
{{ stars(page.route) }}
```

This will use the current page's route as the unique identifier.

### NOTE:

If you wish to use the `{{ stars(id) }}` Twig call in your page's content rather than in a Twig template, **you must disable page cache** ([see how to do this via page headers](https://learn.getgrav.org/content/headers#cache-enable)) or the stars value will be cached and will not sure an accurate representation of the state until the cache is cleared.