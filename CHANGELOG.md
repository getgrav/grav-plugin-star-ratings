# v2.0.1
## 07/18/2017

1. [](#new)
    * Added new option `vote_access` to prevent unhautorized users from voting

# v2.0.0
## 07/18/2017

1. [](#new)
    * Aggregated Rating (Rich Snippets) is now supported, for more details check the documentation [#6](https://github.com/getgrav/grav-plugin-star-ratings/issues/6)
    * Added optional average score display [#7](https://github.com/getgrav/grav-plugin-star-ratings/issues/7)
    * Added new `star_data()` Twig function [#4](https://github.com/getgrav/grav-plugin-star-ratings/issues/4)
1. [](#improved)
    * Updated star-ratings-svg library to latest version (v1.2.0)
    * Added `star_shape` option ('straight' [default], 'rounded')
    * Added `global_initialization` setting to allow global page options for the stars, rather than repeating for all individual items
    * Better handling messages through tooltips when voting or if a vote fails to register
    * Automatically disable ability to vote if already voted and `unique_ip_check` option is enabled
1. [](#bugfix)
    * Fixed `initial_stars` setting not properly getting set through options
    * Fixed issue with `show_count` throwing off the hovering position [#5](https://github.com/getgrav/grav-plugin-star-ratings/issues/5)

# v1.0.4
## 10/18/2016

1. [](#bugfix)
    * Fixed a typo in blueprints for docs

# v1.0.3
## 05/12/2016

1. [](#bugfix)
    * Refactored `addVote()` to work better with cache on
    * Updated README to reference need for cache off when using Twig function in page content

# v1.0.2
## 05/12/2016

1. [](#bugfix)
    * Fixed issue with `Page` being initialize too eary by `onPagesInitialized()` event
    * Force no data to be an empty array

# v1.0.1
## 04/27/2016

1. [](#new)
    * Fixed typos in blueprints

# v1.0.0
## 04/27/2016

1. [](#new)
    * Initial release
