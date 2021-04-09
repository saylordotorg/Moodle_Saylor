/* jshint ignore:start */
define(['jquery', 'core/log',
    'filter_poodll/poodll_basemediaskin',
    'filter_poodll/poodll_onetwothreemediaskin',
    'filter_poodll/poodll_goldmediaskin',
    'filter_poodll/poodll_bmrmediaskin',
    'filter_poodll/poodll_shadowmediaskin',
    'filter_poodll/poodll_splitmediaskin',
    'filter_poodll/poodll_fbmediaskin',
    'filter_poodll/poodll_pushmediaskin',
    'filter_poodll/poodll_readaloudmediaskin',
    'filter_poodll/poodll_readseedmediaskin',
    'filter_poodll/poodll_oncemediaskin',
    'filter_poodll/poodll_freshmediaskin',
    'filter_poodll/poodll_uploadmediaskin',
    'filter_poodll/poodll_warningmediaskin'], function ($, log, baseskin, onetwothreeskin, goldskin, bmrskin, shadowskin, splitskin, fluencybuilderskin, pushskin, readaloudskin, readseedskin, onceskin, freshskin, uploadskin, warningskin) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Media Skins: initialising');

    return {

        fetch_skin_clone: function (skinname) {
            var the_skin = "";

            switch (skinname) {
                case 'onetwothree':
                    the_skin = onetwothreeskin.clone();
                    break;

                case 'gold':
                    the_skin = goldskin.clone();
                    break;

                case 'burntrose':
                case 'bmr':
                    the_skin = bmrskin.clone();
                    break;
                case 'fluencybuilder':
                    the_skin = fluencybuilderskin.clone();
                    break;
                case 'push':
                    the_skin = pushskin.clone();
                    break;
                case 'readaloud':
                    the_skin = readaloudskin.clone();
                    break;
                case 'readseed':
                    the_skin = readseedskin.clone();
                    break;
                case 'shadow':
                    the_skin = shadowskin.clone();
                    break;
                case 'split':
                    the_skin = splitskin.clone();
                    break;
                case 'once':
                    the_skin = onceskin.clone();
                    break;
                case 'fresh':
                    the_skin = freshskin.clone();
                    break;
                case 'upload':
                    the_skin = uploadskin.clone();
                    break;
                case 'warning':
                    the_skin = warningskin.clone();
                    break;
                case 'plain':
                case 'standard':
                default:
                    the_skin = baseskin.clone();
            }
            return the_skin;
        }
    };// end of returned object
});// total end