

YUI.add('moodle-atto_mb2shortcodes-button', function (Y, NAME) {



var COMPONENTNAME = 'atto_mb2shortcodes',
   
   
	CSS = {
        BUTTON: 'atto_mb2shortcodes_character',
        CODECONTAINER: 'atto_mb2shortcodes_selector',
		FORMGROUP: 'atto_mb2shortcodes_formgroup',
		CODEAREA: 'atto_mb2shortcodes_codearea',
		INSERTBTN: 'atto_mb2shortcodes_insertbtn',
		SHORTCODEFORM: 'atto_mb2shortcodes_shortcodeform',
		GENERATEBTN: 'atto_mb2shortcodes_generate',
		CODEAREAFORM: 'atto_mb2shortcodes_codeareaform',
		FORMFIELD: 'atto_mb2shortcodes_formfield',
		FORMFIELDDESC: 'atto_mb2shortcodes_formfielddesc',
		CODEFORM: 'atto_mb2shortcodes_form',
		CODESELECTCONTAINER: 'atto_mb2shortcodes_codeselect_wrap',
		REPEATITEM: 'atto_mb2shortcodes_repeat_item',
		CODEAREA: 'atto_mb2shortcodes_codearea',
		REMOVEREPEAT: 'atto_mb2shortcodes_removeitem' ,
		IMAGEBROWSER: 'atto_mb2shortcodes_imgbrowser',
		IMAGEINPUTURL: 'atto_mb2shortcodes_imgurl',
		ADDREPEAT: 'atto_mb2shortcodes_additem',
		REPEATITEMS: 'atto_mb2shortcodes_ritems',
		REPEATITEMSINNER: 'atto_mb2shortcodes_ritems_inner',
		IMAGEPREVIEW: 'atto_mb2shortcodes_imgpreview',
		IMAGECLEAR: 'atto_mb2shortcodes_img_clear',
		ADDICON: 'atto_mb2shortcodes_addicon',
		ICONTABS: 'atto_mb2shortcodes_iconstab',
		ICONTABSLIST: 'atto_mb2shortcodes_iconstab_list',
		ICONTABSCONTENT: 'atto_mb2shortcodes_iconstab_content',
		ICONTABSCONTENTITEM: 'atto_mb2shortcodes_iconstab_content_item',
		ICONTABSAREA: 'atto_mb2shortcodes_iconstab_area',
		ICONITEM: 'atto_mb2shortcodes_icon_item',
		ICONINPUT: 'atto_mb2shortcodes_icon_input',
		ICONPREVIEW: 'atto_mb2shortcodes_icon_preview',
		REMOVEICON: 'atto_mb2shortcodes_icon_clear',
		BTNSMALLDANGER: 'btn btn-sm btn-danger',
		CODELINK: 'atto_mb2shortcodes_code_link',
		BACKTOCHOOSER: 'atto_mb2shortcodes_code_back'
    },
	
	
	SELECTORS = {
		CODEFORM: '.' + CSS.CODEFORM,
		CODEAREA: '.' + CSS.CODEAREA,
		INSERTBTN: '.' + CSS.INSERTBTN,
		REMOVEREPEAT: '.' + CSS.REMOVEREPEAT,
		ADDREPEAT: '.' + CSS.ADDREPEAT,
		REPEATITEMS: '.' + CSS.REPEATITEMS,
		REPEATITEMSINNER: '.' + CSS.REPEATITEMSINNER,
		IMAGEBROWSER: '.' + CSS.IMAGEBROWSER,
		IMAGEINPUTURL: '.' + CSS.IMAGEINPUTURL,
		FORMFIELD: '.' + CSS.FORMFIELD,
		IMAGEPREVIEW: '.' + CSS.IMAGEPREVIEW,
		IMAGECLEAR: '.' + CSS.IMAGECLEAR,
		ADDICON: '.' + CSS.ADDICON,
		ICONTABS: '.' + CSS.ICONTABS,
		ICONTABSLIST: '.' + CSS.ICONTABSLIST,
		ICONTABSCONTENT: '.' + CSS.ICONTABSCONTENT,
		ICONTABSCONTENTITEM: '.' + CSS.ICONTABSCONTENTITEM,
		ICONTABSAREA: '.' + CSS.ICONTABSAREA,
		ICONITEM: '.' + CSS.ICONITEM,
		ICONINPUT: '.' + CSS.ICONINPUT,
		ICONPREVIEW: '.' + CSS.ICONPREVIEW+' i',
		REMOVEICON: '.' + CSS.REMOVEICON,
		CODELINK: '.' + CSS.CODELINK,
		BACKTOCHOOSER: '.' + CSS.BACKTOCHOOSER		
	},
    
	
	
	
	
	ICONSFA = [		
		{name: 'fa-glass'},
		{name: 'fa-music'},
		{name: 'fa-search'},
		{name: 'fa-envelope-o'},
		{name: 'fa-heart'},
		{name: 'fa-star'},
		{name: 'fa-star-o'},
		{name: 'fa-user'},
		{name: 'fa-film'},
		{name: 'fa-th-large'},
		{name: 'fa-th'},
		{name: 'fa-th-list'},
		{name: 'fa-check'},
		{name: 'fa-times'},
		{name: 'fa-search-plus'},
		{name: 'fa-search-minus'},
		{name: 'fa-power-off'},
		{name: 'fa-signal'},
		{name: 'fa-cog'},
		{name: 'fa-trash-o'},
		{name: 'fa-home'},
		{name: 'fa-file-o'},
		{name: 'fa-clock-o'},
		{name: 'fa-road'},
		{name: 'fa-download'},
		{name: 'fa-arrow-circle-o-down'},
		{name: 'fa-arrow-circle-o-up'},
		{name: 'fa-inbox'},
		{name: 'fa-play-circle-o'},
		{name: 'fa-repeat'},
		{name: 'fa-refresh'},
		{name: 'fa-list-alt'},
		{name: 'fa-lock'},
		{name: 'fa-flag'},
		{name: 'fa-headphones'},
		{name: 'fa-volume-off'},
		{name: 'fa-volume-down'},
		{name: 'fa-volume-up'},
		{name: 'fa-qrcode'},
		{name: 'fa-barcode'},
		{name: 'fa-tag'},
		{name: 'fa-tags'},
		{name: 'fa-book'},
		{name: 'fa-bookmark'},
		{name: 'fa-print'},
		{name: 'fa-camera'},
		{name: 'fa-font'},
		{name: 'fa-bold'},
		{name: 'fa-italic'},
		{name: 'fa-text-height'},
		{name: 'fa-text-width'},
		{name: 'fa-align-left'},
		{name: 'fa-align-center'},
		{name: 'fa-align-right'},
		{name: 'fa-align-justify'},
		{name: 'fa-list'},
		{name: 'fa-outdent'},
		{name: 'fa-indent'},
		{name: 'fa-video-camera'},
		{name: 'fa-picture-o'},
		{name: 'fa-pencil'},
		{name: 'fa-map-marker'},
		{name: 'fa-adjust'},
		{name: 'fa-tint'},
		{name: 'fa-pencil-square-o'},
		{name: 'fa-share-square-o'},
		{name: 'fa-check-square-o'},
		{name: 'fa-arrows'},
		{name: 'fa-step-backward'},
		{name: 'fa-fast-backward'},
		{name: 'fa-backward'},
		{name: 'fa-play'},
		{name: 'fa-pause'},
		{name: 'fa-stop'},
		{name: 'fa-forward'},
		{name: 'fa-fast-forward'},
		{name: 'fa-step-forward'},
		{name: 'fa-eject'},
		{name: 'fa-chevron-left'},
		{name: 'fa-chevron-right'},
		{name: 'fa-plus-circle'},
		{name: 'fa-minus-circle'},
		{name: 'fa-times-circle'},
		{name: 'fa-check-circle'},
		{name: 'fa-question-circle'},
		{name: 'fa-info-circle'},
		{name: 'fa-crosshairs'},
		{name: 'fa-times-circle-o'},
		{name: 'fa-check-circle-o'},
		{name: 'fa-ban'},
		{name: 'fa-arrow-left'},
		{name: 'fa-arrow-right'},
		{name: 'fa-arrow-up'},
		{name: 'fa-arrow-down'},
		{name: 'fa-share'},
		{name: 'fa-expand'},
		{name: 'fa-compress'},
		{name: 'fa-plus'},
		{name: 'fa-minus'},
		{name: 'fa-asterisk'},
		{name: 'fa-exclamation-circle'},
		{name: 'fa-gift'},
		{name: 'fa-leaf'},
		{name: 'fa-fire'},
		{name: 'fa-eye'},
		{name: 'fa-eye-slash'},
		{name: 'fa-exclamation-triangle'},
		{name: 'fa-plane'},
		{name: 'fa-calendar'},
		{name: 'fa-random'},
		{name: 'fa-comment'},
		{name: 'fa-magnet'},
		{name: 'fa-chevron-up'},
		{name: 'fa-chevron-down'},
		{name: 'fa-retweet'},
		{name: 'fa-shopping-cart'},
		{name: 'fa-folder'},
		{name: 'fa-folder-open'},
		{name: 'fa-arrows-v'},
		{name: 'fa-arrows-h'},
		{name: 'fa-bar-chart'},
		{name: 'fa-twitter-square'},
		{name: 'fa-facebook-square'},
		{name: 'fa-camera-retro'},
		{name: 'fa-key'},
		{name: 'fa-cogs'},
		{name: 'fa-comments'},
		{name: 'fa-thumbs-o-up'},
		{name: 'fa-thumbs-o-down'},
		{name: 'fa-star-half'},
		{name: 'fa-heart-o'},
		{name: 'fa-sign-out'},
		{name: 'fa-linkedin-square'},
		{name: 'fa-thumb-tack'},
		{name: 'fa-external-link'},
		{name: 'fa-sign-in'},
		{name: 'fa-trophy'},
		{name: 'fa-github-square'},
		{name: 'fa-upload'},
		{name: 'fa-lemon-o'},
		{name: 'fa-phone'},
		{name: 'fa-square-o'},
		{name: 'fa-bookmark-o'},
		{name: 'fa-phone-square'},
		{name: 'fa-twitter'},
		{name: 'fa-facebook'},
		{name: 'fa-github'},
		{name: 'fa-unlock'},
		{name: 'fa-credit-card'},
		{name: 'fa-rss'},
		{name: 'fa-hdd-o'},
		{name: 'fa-bullhorn'},
		{name: 'fa-bell'},
		{name: 'fa-certificate'},
		{name: 'fa-hand-o-right'},
		{name: 'fa-hand-o-left'},
		{name: 'fa-hand-o-up'},
		{name: 'fa-hand-o-down'},
		{name: 'fa-arrow-circle-left'},
		{name: 'fa-arrow-circle-right'},
		{name: 'fa-arrow-circle-up'},
		{name: 'fa-arrow-circle-down'},
		{name: 'fa-globe'},
		{name: 'fa-wrench'},
		{name: 'fa-tasks'},
		{name: 'fa-filter'},
		{name: 'fa-briefcase'},
		{name: 'fa-arrows-alt'},
		{name: 'fa-users'},
		{name: 'fa-link'},
		{name: 'fa-cloud'},
		{name: 'fa-flask'},
		{name: 'fa-scissors'},
		{name: 'fa-files-o'},
		{name: 'fa-paperclip'},
		{name: 'fa-floppy-o'},
		{name: 'fa-square'},
		{name: 'fa-bars'},
		{name: 'fa-list-ul'},
		{name: 'fa-list-ol'},
		{name: 'fa-strikethrough'},
		{name: 'fa-underline'},
		{name: 'fa-table'},
		{name: 'fa-magic'},
		{name: 'fa-truck'},
		{name: 'fa-pinterest'},
		{name: 'fa-pinterest-square'},
		{name: 'fa-google-plus-square'},
		{name: 'fa-google-plus'},
		{name: 'fa-money'},
		{name: 'fa-caret-down'},
		{name: 'fa-caret-up'},
		{name: 'fa-caret-left'},
		{name: 'fa-caret-right'},
		{name: 'fa-columns'},
		{name: 'fa-sort'},
		{name: 'fa-sort-desc'},
		{name: 'fa-sort-asc'},
		{name: 'fa-envelope'},
		{name: 'fa-linkedin'},
		{name: 'fa-undo'},
		{name: 'fa-gavel'},
		{name: 'fa-tachometer'},
		{name: 'fa-comment-o'},
		{name: 'fa-comments-o'},
		{name: 'fa-bolt'},
		{name: 'fa-sitemap'},
		{name: 'fa-umbrella'},
		{name: 'fa-clipboard'},
		{name: 'fa-lightbulb-o'},
		{name: 'fa-exchange'},
		{name: 'fa-cloud-download'},
		{name: 'fa-cloud-upload'},
		{name: 'fa-user-md'},
		{name: 'fa-stethoscope'},
		{name: 'fa-suitcase'},
		{name: 'fa-bell-o'},
		{name: 'fa-coffee'},
		{name: 'fa-cutlery'},
		{name: 'fa-file-text-o'},
		{name: 'fa-building-o'},
		{name: 'fa-hospital-o'},
		{name: 'fa-ambulance'},
		{name: 'fa-medkit'},
		{name: 'fa-fighter-jet'},
		{name: 'fa-beer'},
		{name: 'fa-h-square'},
		{name: 'fa-plus-square'},
		{name: 'fa-angle-double-left'},
		{name: 'fa-angle-double-right'},
		{name: 'fa-angle-double-up'},
		{name: 'fa-angle-double-down'},
		{name: 'fa-angle-left'},
		{name: 'fa-angle-right'},
		{name: 'fa-angle-up'},
		{name: 'fa-angle-down'},
		{name: 'fa-desktop'},
		{name: 'fa-laptop'},
		{name: 'fa-tablet'},
		{name: 'fa-mobile'},
		{name: 'fa-circle-o'},
		{name: 'fa-quote-left'},
		{name: 'fa-quote-right'},
		{name: 'fa-spinner'},
		{name: 'fa-circle'},
		{name: 'fa-reply'},
		{name: 'fa-github-alt'},
		{name: 'fa-folder-o'},
		{name: 'fa-folder-open-o'},
		{name: 'fa-smile-o'},
		{name: 'fa-frown-o'},
		{name: 'fa-meh-o'},
		{name: 'fa-gamepad'},
		{name: 'fa-keyboard-o'},
		{name: 'fa-flag-o'},
		{name: 'fa-flag-checkered'},
		{name: 'fa-terminal'},
		{name: 'fa-code'},
		{name: 'fa-reply-all'},
		{name: 'fa-star-half-o'},
		{name: 'fa-location-arrow'},
		{name: 'fa-crop'},
		{name: 'fa-code-fork'},
		{name: 'fa-chain-broken'},
		{name: 'fa-question'},
		{name: 'fa-info'},
		{name: 'fa-exclamation'},
		{name: 'fa-superscript'},
		{name: 'fa-subscript'},
		{name: 'fa-eraser'},
		{name: 'fa-puzzle-piece'},
		{name: 'fa-microphone'},
		{name: 'fa-microphone-slash'},
		{name: 'fa-shield'},
		{name: 'fa-calendar-o'},
		{name: 'fa-fire-extinguisher'},
		{name: 'fa-rocket'},
		{name: 'fa-maxcdn'},
		{name: 'fa-chevron-circle-left'},
		{name: 'fa-chevron-circle-right'},
		{name: 'fa-chevron-circle-up'},
		{name: 'fa-chevron-circle-down'},
		{name: 'fa-html5'},
		{name: 'fa-css3'},
		{name: 'fa-anchor'},
		{name: 'fa-unlock-alt'},
		{name: 'fa-bullseye'},
		{name: 'fa-ellipsis-h'},
		{name: 'fa-ellipsis-v'},
		{name: 'fa-rss-square'},
		{name: 'fa-play-circle'},
		{name: 'fa-ticket'},
		{name: 'fa-minus-square'},
		{name: 'fa-minus-square-o'},
		{name: 'fa-level-up'},
		{name: 'fa-level-down'},
		{name: 'fa-check-square'},
		{name: 'fa-pencil-square'},
		{name: 'fa-external-link-square'},
		{name: 'fa-share-square'},
		{name: 'fa-compass'},
		{name: 'fa-caret-square-o-down'},
		{name: 'fa-caret-square-o-up'},
		{name: 'fa-caret-square-o-right'},
		{name: 'fa-eur'},
		{name: 'fa-gbp'},
		{name: 'fa-usd'},
		{name: 'fa-inr'},
		{name: 'fa-jpy'},
		{name: 'fa-rub'},
		{name: 'fa-krw'},
		{name: 'fa-btc'},
		{name: 'fa-file'},
		{name: 'fa-file-text'},
		{name: 'fa-sort-alpha-asc'},
		{name: 'fa-sort-alpha-desc'},
		{name: 'fa-sort-amount-asc'},
		{name: 'fa-sort-amount-desc'},
		{name: 'fa-sort-numeric-asc'},
		{name: 'fa-sort-numeric-desc'},
		{name: 'fa-thumbs-up'},
		{name: 'fa-thumbs-down'},
		{name: 'fa-youtube-square'},
		{name: 'fa-youtube'},
		{name: 'fa-xing'},
		{name: 'fa-xing-square'},
		{name: 'fa-youtube-play'},
		{name: 'fa-dropbox'},
		{name: 'fa-stack-overflow'},
		{name: 'fa-instagram'},
		{name: 'fa-flickr'},
		{name: 'fa-adn'},
		{name: 'fa-bitbucket'},
		{name: 'fa-bitbucket-square'},
		{name: 'fa-tumblr'},
		{name: 'fa-tumblr-square'},
		{name: 'fa-long-arrow-down'},
		{name: 'fa-long-arrow-up'},
		{name: 'fa-long-arrow-left'},
		{name: 'fa-long-arrow-right'},
		{name: 'fa-apple'},
		{name: 'fa-windows'},
		{name: 'fa-android'},
		{name: 'fa-linux'},
		{name: 'fa-dribbble'},
		{name: 'fa-skype'},
		{name: 'fa-foursquare'},
		{name: 'fa-trello'},
		{name: 'fa-female'},
		{name: 'fa-male'},
		{name: 'fa-gratipay'},
		{name: 'fa-sun-o'},
		{name: 'fa-moon-o'},
		{name: 'fa-archive'},
		{name: 'fa-bug'},
		{name: 'fa-vk'},
		{name: 'fa-weibo'},
		{name: 'fa-renren'},
		{name: 'fa-pagelines'},
		{name: 'fa-stack-exchange'},
		{name: 'fa-arrow-circle-o-right'},
		{name: 'fa-arrow-circle-o-left'},
		{name: 'fa-caret-square-o-left'},
		{name: 'fa-dot-circle-o'},
		{name: 'fa-wheelchair'},
		{name: 'fa-vimeo-square'},
		{name: 'fa-try'},
		{name: 'fa-plus-square-o'},
		{name: 'fa-space-shuttle'},
		{name: 'fa-slack'},
		{name: 'fa-envelope-square'},
		{name: 'fa-wordpress'},
		{name: 'fa-openid'},
		{name: 'fa-university'},
		{name: 'fa-graduation-cap'},
		{name: 'fa-yahoo'},
		{name: 'fa-google'},
		{name: 'fa-reddit'},
		{name: 'fa-reddit-square'},
		{name: 'fa-stumbleupon-circle'},
		{name: 'fa-stumbleupon'},
		{name: 'fa-delicious'},
		{name: 'fa-digg'},
		{name: 'fa-pied-piper-pp'},
		{name: 'fa-pied-piper-alt'},
		{name: 'fa-drupal'},
		{name: 'fa-joomla'},
		{name: 'fa-language'},
		{name: 'fa-fax'},
		{name: 'fa-building'},
		{name: 'fa-child'},
		{name: 'fa-paw'},
		{name: 'fa-spoon'},
		{name: 'fa-cube'},
		{name: 'fa-cubes'},
		{name: 'fa-behance'},
		{name: 'fa-behance-square'},
		{name: 'fa-steam'},
		{name: 'fa-steam-square'},
		{name: 'fa-recycle'},
		{name: 'fa-car'},
		{name: 'fa-taxi'},
		{name: 'fa-tree'},
		{name: 'fa-spotify'},
		{name: 'fa-deviantart'},
		{name: 'fa-soundcloud'},
		{name: 'fa-database'},
		{name: 'fa-file-pdf-o'},
		{name: 'fa-file-word-o'},
		{name: 'fa-file-excel-o'},
		{name: 'fa-file-powerpoint-o'},
		{name: 'fa-file-image-o'},
		{name: 'fa-file-archive-o'},
		{name: 'fa-file-audio-o'},
		{name: 'fa-file-video-o'},
		{name: 'fa-file-code-o'},
		{name: 'fa-vine'},
		{name: 'fa-codepen'},
		{name: 'fa-jsfiddle'},
		{name: 'fa-life-ring'},
		{name: 'fa-circle-o-notch'},
		{name: 'fa-rebel'},
		{name: 'fa-empire'},
		{name: 'fa-git-square'},
		{name: 'fa-git'},
		{name: 'fa-hacker-news'},
		{name: 'fa-tencent-weibo'},
		{name: 'fa-qq'},
		{name: 'fa-weixin'},
		{name: 'fa-paper-plane'},
		{name: 'fa-paper-plane-o'},
		{name: 'fa-history'},
		{name: 'fa-circle-thin'},
		{name: 'fa-header'},
		{name: 'fa-paragraph'},
		{name: 'fa-sliders'},
		{name: 'fa-share-alt'},
		{name: 'fa-share-alt-square'},
		{name: 'fa-bomb'},
		{name: 'fa-futbol-o'},
		{name: 'fa-tty'},
		{name: 'fa-binoculars'},
		{name: 'fa-plug'},
		{name: 'fa-slideshare'},
		{name: 'fa-twitch'},
		{name: 'fa-yelp'},
		{name: 'fa-newspaper-o'},
		{name: 'fa-wifi'},
		{name: 'fa-calculator'},
		{name: 'fa-paypal'},
		{name: 'fa-google-wallet'},
		{name: 'fa-cc-visa'},
		{name: 'fa-cc-mastercard'},
		{name: 'fa-cc-discover'},
		{name: 'fa-cc-amex'},
		{name: 'fa-cc-paypal'},
		{name: 'fa-cc-stripe'},
		{name: 'fa-bell-slash'},
		{name: 'fa-bell-slash-o'},
		{name: 'fa-trash'},
		{name: 'fa-copyright'},
		{name: 'fa-at'},
		{name: 'fa-eyedropper'},
		{name: 'fa-paint-brush'},
		{name: 'fa-birthday-cake'},
		{name: 'fa-area-chart'},
		{name: 'fa-pie-chart'},
		{name: 'fa-line-chart'},
		{name: 'fa-lastfm'},
		{name: 'fa-lastfm-square'},
		{name: 'fa-toggle-off'},
		{name: 'fa-toggle-on'},
		{name: 'fa-bicycle'},
		{name: 'fa-bus'},
		{name: 'fa-ioxhost'},
		{name: 'fa-angellist'},
		{name: 'fa-cc'},
		{name: 'fa-ils'},
		{name: 'fa-meanpath'},
		{name: 'fa-buysellads'},
		{name: 'fa-connectdevelop'},
		{name: 'fa-dashcube'},
		{name: 'fa-forumbee'},
		{name: 'fa-leanpub'},
		{name: 'fa-sellsy'},
		{name: 'fa-shirtsinbulk'},
		{name: 'fa-simplybuilt'},
		{name: 'fa-skyatlas'},
		{name: 'fa-cart-plus'},
		{name: 'fa-cart-arrow-down'},
		{name: 'fa-diamond'},
		{name: 'fa-ship'},
		{name: 'fa-user-secret'},
		{name: 'fa-motorcycle'},
		{name: 'fa-street-view'},
		{name: 'fa-heartbeat'},
		{name: 'fa-venus'},
		{name: 'fa-mars'},
		{name: 'fa-mercury'},
		{name: 'fa-transgender'},
		{name: 'fa-transgender-alt'},
		{name: 'fa-venus-double'},
		{name: 'fa-mars-double'},
		{name: 'fa-venus-mars'},
		{name: 'fa-mars-stroke'},
		{name: 'fa-mars-stroke-v'},
		{name: 'fa-mars-stroke-h'},
		{name: 'fa-neuter'},
		{name: 'fa-genderless'},
		{name: 'fa-facebook-official'},
		{name: 'fa-pinterest-p'},
		{name: 'fa-whatsapp'},
		{name: 'fa-server'},
		{name: 'fa-user-plus'},
		{name: 'fa-user-times'},
		{name: 'fa-bed'},
		{name: 'fa-viacoin'},
		{name: 'fa-train'},
		{name: 'fa-subway'},
		{name: 'fa-medium'},
		{name: 'fa-y-combinator'},
		{name: 'fa-optin-monster'},
		{name: 'fa-opencart'},
		{name: 'fa-expeditedssl'},
		{name: 'fa-battery-full'},
		{name: 'fa-battery-three-quarters'},
		{name: 'fa-battery-half'},
		{name: 'fa-battery-quarter'},
		{name: 'fa-battery-empty'},
		{name: 'fa-mouse-pointer'},
		{name: 'fa-i-cursor'},
		{name: 'fa-object-group'},
		{name: 'fa-object-ungroup'},
		{name: 'fa-sticky-note'},
		{name: 'fa-sticky-note-o'},
		{name: 'fa-cc-jcb'},
		{name: 'fa-cc-diners-club'},
		{name: 'fa-clone'},
		{name: 'fa-balance-scale'},
		{name: 'fa-hourglass-o'},
		{name: 'fa-hourglass-start'},
		{name: 'fa-hourglass-half'},
		{name: 'fa-hourglass-end'},
		{name: 'fa-hourglass'},
		{name: 'fa-hand-rock-o'},
		{name: 'fa-hand-paper-o'},
		{name: 'fa-hand-scissors-o'},
		{name: 'fa-hand-lizard-o'},
		{name: 'fa-hand-spock-o'},
		{name: 'fa-hand-pointer-o'},
		{name: 'fa-hand-peace-o'},
		{name: 'fa-trademark'},
		{name: 'fa-registered'},
		{name: 'fa-creative-commons'},
		{name: 'fa-gg'},
		{name: 'fa-gg-circle'},
		{name: 'fa-tripadvisor'},
		{name: 'fa-odnoklassniki'},
		{name: 'fa-odnoklassniki-square'},
		{name: 'fa-get-pocket'},
		{name: 'fa-wikipedia-w'},
		{name: 'fa-safari'},
		{name: 'fa-chrome'},
		{name: 'fa-firefox'},
		{name: 'fa-opera'},
		{name: 'fa-internet-explorer'},
		{name: 'fa-television'},
		{name: 'fa-contao'},
		{name: 'fa-500px'},
		{name: 'fa-amazon'},
		{name: 'fa-calendar-plus-o'},
		{name: 'fa-calendar-minus-o'},
		{name: 'fa-calendar-times-o'},
		{name: 'fa-calendar-check-o'},
		{name: 'fa-industry'},
		{name: 'fa-map-pin'},
		{name: 'fa-map-signs'},
		{name: 'fa-map-o'},
		{name: 'fa-map'},
		{name: 'fa-commenting'},
		{name: 'fa-commenting-o'},
		{name: 'fa-houzz'},
		{name: 'fa-vimeo'},
		{name: 'fa-black-tie'},
		{name: 'fa-fonticons'},
		{name: 'fa-reddit-alien'},
		{name: 'fa-edge'},
		{name: 'fa-credit-card-alt'},
		{name: 'fa-codiepie'},
		{name: 'fa-modx'},
		{name: 'fa-fort-awesome'},
		{name: 'fa-usb'},
		{name: 'fa-product-hunt'},
		{name: 'fa-mixcloud'},
		{name: 'fa-scribd'},
		{name: 'fa-pause-circle'},
		{name: 'fa-pause-circle-o'},
		{name: 'fa-stop-circle'},
		{name: 'fa-stop-circle-o'},
		{name: 'fa-shopping-bag'},
		{name: 'fa-shopping-basket'},
		{name: 'fa-hashtag'},
		{name: 'fa-bluetooth'},
		{name: 'fa-bluetooth-b'},
		{name: 'fa-percent'},
		{name: 'fa-gitlab'},
		{name: 'fa-wpbeginner'},
		{name: 'fa-wpforms'},
		{name: 'fa-envira'},
		{name: 'fa-universal-access'},
		{name: 'fa-wheelchair-alt'},
		{name: 'fa-question-circle-o'},
		{name: 'fa-blind'},
		{name: 'fa-audio-description'},
		{name: 'fa-volume-control-phone'},
		{name: 'fa-braille'},
		{name: 'fa-assistive-listening-systems'},
		{name: 'fa-american-sign-language-interpreting'},
		{name: 'fa-deaf'},
		{name: 'fa-glide'},
		{name: 'fa-glide-g'},
		{name: 'fa-sign-language'},
		{name: 'fa-low-vision'},
		{name: 'fa-viadeo'},
		{name: 'fa-viadeo-square'},
		{name: 'fa-snapchat'},
		{name: 'fa-snapchat-ghost'},
		{name: 'fa-snapchat-square'},
		{name: 'fa-pied-piper'},
		{name: 'fa-first-order'},
		{name: 'fa-yoast'},
		{name: 'fa-themeisle'},
		{name: 'fa-google-plus-official'},
		{name: 'fa-font-awesome'},
		{name: 'fa-handshake-o'},
		{name: 'fa-envelope-open'},
		{name: 'fa-envelope-open-o'},
		{name: 'fa-linode'},
		{name: 'fa-address-book'},
		{name: 'fa-address-book-o'},
		{name: 'fa-address-card'},
		{name: 'fa-address-card-o'},
		{name: 'fa-user-circle'},
		{name: 'fa-user-circle-o'},
		{name: 'fa-user-o'},
		{name: 'fa-id-badge'},
		{name: 'fa-id-card'},
		{name: 'fa-id-card-o'},
		{name: 'fa-quora'},
		{name: 'fa-free-code-camp'},
		{name: 'fa-telegram'},
		{name: 'fa-thermometer-full'},
		{name: 'fa-thermometer-three-quarters'},
		{name: 'fa-thermometer-half'},
		{name: 'fa-thermometer-quarter'},
		{name: 'fa-thermometer-empty'},
		{name: 'fa-shower'},
		{name: 'fa-bath'},
		{name: 'fa-podcast'},
		{name: 'fa-window-maximize'},
		{name: 'fa-window-minimize'},
		{name: 'fa-window-restore'},
		{name: 'fa-window-close'},
		{name: 'fa-window-close-o'},
		{name: 'fa-bandcamp'},
		{name: 'fa-grav'},
		{name: 'fa-etsy'},
		{name: 'fa-imdb'},
		{name: 'fa-ravelry'},
		{name: 'fa-eercast'},
		{name: 'fa-microchip'},
		{name: 'fa-snowflake-o'},
		{name: 'fa-superpowers'},
		{name: 'fa-wpexplorer'},
		{name: 'fa-meetup'}    
   ],
   
   
   
   ICONS7STROKE = [		
		{name: 'pe-7s-album'},
		{name: 'pe-7s-arc'},
		{name: 'pe-7s-back-2'},
		{name: 'pe-7s-bandaid'},
		{name: 'pe-7s-car'},
		{name: 'pe-7s-diamond'},
		{name: 'pe-7s-door-lock'},
		{name: 'pe-7s-eyedropper'},
		{name: 'pe-7s-female'},
		{name: 'pe-7s-gym'},
		{name: 'pe-7s-hammer'},
		{name: 'pe-7s-headphones'},
		{name: 'pe-7s-helm'},
		{name: 'pe-7s-hourglass'},
		{name: 'pe-7s-leaf'},
		{name: 'pe-7s-magic-wand'},
		{name: 'pe-7s-male'},
		{name: 'pe-7s-map-2'},
		{name: 'pe-7s-next-2'},
		{name: 'pe-7s-paint-bucket'},
		{name: 'pe-7s-pendrive'},
		{name: 'pe-7s-photo'},
		{name: 'pe-7s-piggy'},
		{name: 'pe-7s-plugin'},
		{name: 'pe-7s-refresh-2'},
		{name: 'pe-7s-rocket'},
		{name: 'pe-7s-settings'},
		{name: 'pe-7s-shield'},
		{name: 'pe-7s-smile'},
		{name: 'pe-7s-usb'},
		{name: 'pe-7s-vector'},
		{name: 'pe-7s-wine'},
		{name: 'pe-7s-cloud-upload'},
		{name: 'pe-7s-cash'},
		{name: 'pe-7s-close'},
		{name: 'pe-7s-bluetooth'},
		{name: 'pe-7s-cloud-download'},
		{name: 'pe-7s-way'},
		{name: 'pe-7s-close-circle'},
		{name: 'pe-7s-id'},
		{name: 'pe-7s-angle-up'},
		{name: 'pe-7s-wristwatch'},
		{name: 'pe-7s-angle-up-circle'},
		{name: 'pe-7s-world'},
		{name: 'pe-7s-angle-right'},
		{name: 'pe-7s-volume'},
		{name: 'pe-7s-angle-right-circle'},
		{name: 'pe-7s-users'},
		{name: 'pe-7s-angle-left'},
		{name: 'pe-7s-user-female'},
		{name: 'pe-7s-angle-left-circle'},
		{name: 'pe-7s-up-arrow'},
		{name: 'pe-7s-angle-down'},
		{name: 'pe-7s-switch'},
		{name: 'pe-7s-angle-down-circle'},
		{name: 'pe-7s-scissors'},
		{name: 'pe-7s-wallet'},
		{name: 'pe-7s-safe'},
		{name: 'pe-7s-volume2'},
		{name: 'pe-7s-volume1'},
		{name: 'pe-7s-voicemail'},
		{name: 'pe-7s-video'},
		{name: 'pe-7s-user'},
		{name: 'pe-7s-upload'},
		{name: 'pe-7s-unlock'},
		{name: 'pe-7s-umbrella'},
		{name: 'pe-7s-trash'},
		{name: 'pe-7s-tools'},
		{name: 'pe-7s-timer'},
		{name: 'pe-7s-ticket'},
		{name: 'pe-7s-target'},
		{name: 'pe-7s-sun'},
		{name: 'pe-7s-study'},
		{name: 'pe-7s-stopwatch'},
		{name: 'pe-7s-star'},
		{name: 'pe-7s-speaker'},
		{name: 'pe-7s-signal'},
		{name: 'pe-7s-shuffle'},
		{name: 'pe-7s-shopbag'},
		{name: 'pe-7s-share'},
		{name: 'pe-7s-server'},
		{name: 'pe-7s-search'},
		{name: 'pe-7s-film'},
		{name: 'pe-7s-science'},
		{name: 'pe-7s-disk'},
		{name: 'pe-7s-ribbon'},
		{name: 'pe-7s-repeat'},
		{name: 'pe-7s-refresh'},
		{name: 'pe-7s-add-user'},
		{name: 'pe-7s-refresh-cloud'},
		{name: 'pe-7s-paperclip'},
		{name: 'pe-7s-radio'},
		{name: 'pe-7s-note2'},
		{name: 'pe-7s-print'},
		{name: 'pe-7s-network'},
		{name: 'pe-7s-prev'},
		{name: 'pe-7s-mute'},
		{name: 'pe-7s-power'},
		{name: 'pe-7s-medal'},
		{name: 'pe-7s-portfolio'},
		{name: 'pe-7s-like2'},
		{name: 'pe-7s-plus'},
		{name: 'pe-7s-left-arrow'},
		{name: 'pe-7s-play'},
		{name: 'pe-7s-key'},
		{name: 'pe-7s-plane'},
		{name: 'pe-7s-joy'},
		{name: 'pe-7s-photo-gallery'},
		{name: 'pe-7s-pin'},
		{name: 'pe-7s-phone'},
		{name: 'pe-7s-plug'},
		{name: 'pe-7s-pen'},
		{name: 'pe-7s-right-arrow'},
		{name: 'pe-7s-paper-plane'},
		{name: 'pe-7s-delete-user'},
		{name: 'pe-7s-paint'},
		{name: 'pe-7s-bottom-arrow'},
		{name: 'pe-7s-notebook'},
		{name: 'pe-7s-note'},
		{name: 'pe-7s-next'},
		{name: 'pe-7s-news-paper'},
		{name: 'pe-7s-musiclist'},
		{name: 'pe-7s-music'},
		{name: 'pe-7s-mouse'},
		{name: 'pe-7s-more'},
		{name: 'pe-7s-moon'},
		{name: 'pe-7s-monitor'},
		{name: 'pe-7s-micro'},
		{name: 'pe-7s-menu'},
		{name: 'pe-7s-map'},
		{name: 'pe-7s-map-marker'},
		{name: 'pe-7s-mail'},
		{name: 'pe-7s-mail-open'},
		{name: 'pe-7s-mail-open-file'},
		{name: 'pe-7s-magnet'},
		{name: 'pe-7s-loop'},
		{name: 'pe-7s-look'},
		{name: 'pe-7s-lock'},
		{name: 'pe-7s-lintern'},
		{name: 'pe-7s-link'},
		{name: 'pe-7s-like'},
		{name: 'pe-7s-light'},
		{name: 'pe-7s-less'},
		{name: 'pe-7s-keypad'},
		{name: 'pe-7s-junk'},
		{name: 'pe-7s-info'},
		{name: 'pe-7s-home'},
		{name: 'pe-7s-help2'},
		{name: 'pe-7s-help1'},
		{name: 'pe-7s-graph3'},
		{name: 'pe-7s-graph2'},
		{name: 'pe-7s-graph1'},
		{name: 'pe-7s-graph'},
		{name: 'pe-7s-global'},
		{name: 'pe-7s-gleam'},
		{name: 'pe-7s-glasses'},
		{name: 'pe-7s-gift'},
		{name: 'pe-7s-folder'},
		{name: 'pe-7s-flag'},
		{name: 'pe-7s-filter'},
		{name: 'pe-7s-file'},
		{name: 'pe-7s-expand1'},
		{name: 'pe-7s-exapnd2'},
		{name: 'pe-7s-edit'},
		{name: 'pe-7s-drop'},
		{name: 'pe-7s-drawer'},
		{name: 'pe-7s-download'},
		{name: 'pe-7s-display2'},
		{name: 'pe-7s-display1'},
		{name: 'pe-7s-diskette'},
		{name: 'pe-7s-date'},
		{name: 'pe-7s-cup'},
		{name: 'pe-7s-culture'},
		{name: 'pe-7s-crop'},
		{name: 'pe-7s-credit'},
		{name: 'pe-7s-copy-file'},
		{name: 'pe-7s-config'},
		{name: 'pe-7s-compass'},
		{name: 'pe-7s-comment'},
		{name: 'pe-7s-coffee'},
		{name: 'pe-7s-cloud'},
		{name: 'pe-7s-clock'},
		{name: 'pe-7s-check'},
		{name: 'pe-7s-chat'},
		{name: 'pe-7s-cart'},
		{name: 'pe-7s-camera'},
		{name: 'pe-7s-call'},
		{name: 'pe-7s-calculator'},
		{name: 'pe-7s-browser'},
		{name: 'pe-7s-box2'},
		{name: 'pe-7s-box1'},
		{name: 'pe-7s-bookmarks'},
		{name: 'pe-7s-bicycle'},
		{name: 'pe-7s-bell'},
		{name: 'pe-7s-battery'},
		{name: 'pe-7s-ball'},
		{name: 'pe-7s-back'},
		{name: 'pe-7s-attention'},
		{name: 'pe-7s-anchor'},
		{name: 'pe-7s-albums'},
		{name: 'pe-7s-alarm'},
		{name: 'pe-7s-airplay'}   
	],
   
   	
	
	//nline
	
	OPT_YESNO0 = [
		{value: '', title: M.util.get_string('no', COMPONENTNAME)},
		{value: '1', title: M.util.get_string('yes', COMPONENTNAME)}
	]
	
	OPT_YESNO1 = [
		{value: '', title: M.util.get_string('yes', COMPONENTNAME)},
		{value: '0', title: M.util.get_string('no', COMPONENTNAME)}
	]
	
	
	OPT_LINK_TARGET = [
		{value: '', title: M.util.get_string('link_target_self', COMPONENTNAME)},
		{value: '_blank', title: M.util.get_string('link_target_blank', COMPONENTNAME)}						
	]
	
	
	
	
	CODE_LIST = [
	
		{id: 'accordion', icon: 'fa fa-bars', name: M.util.get_string('accordion', COMPONENTNAME)},
		{id: 'boxcontent', icon: 'fa fa-commenting', name: M.util.get_string('boxcontent', COMPONENTNAME)},
		{id: 'boxicon', icon: 'fa fa-rocket', name: M.util.get_string('boxicon', COMPONENTNAME)},
		{id: 'boximg', icon: 'fa fa-object-group', name: M.util.get_string('boximg', COMPONENTNAME)},		
		{id: 'button', icon: 'fa fa-link', name: M.util.get_string('button', COMPONENTNAME)},
		{id: 'columns', icon: 'fa fa-columns', name: M.util.get_string('columns', COMPONENTNAME)},	
		{id: 'gap', icon: 'fa fa-arrows-v', name: M.util.get_string('gap', COMPONENTNAME)},	
		{id: 'header', icon: 'fa fa-window-maximize', name: M.util.get_string('header', COMPONENTNAME)},	
		{id: 'headings', icon: 'fa fa-header', name: M.util.get_string('headings', COMPONENTNAME)},
		{id: 'highlight', icon: 'fa fa-asterisk', name: M.util.get_string('highlight', COMPONENTNAME)},			
		{id: 'icon', icon: 'fa fa-heart', name: M.util.get_string('icon', COMPONENTNAME)},
		{id: 'image', icon: 'fa fa-picture-o', name: M.util.get_string('image', COMPONENTNAME)},
		{id: 'line', icon: 'fa fa-scissors', name: M.util.get_string('line', COMPONENTNAME)},
		{id: 'list', icon: 'fa fa-list', name: M.util.get_string('list', COMPONENTNAME)},
		{id: 'slider', icon: 'fa fa-arrows-h', name: M.util.get_string('slider', COMPONENTNAME)},
		{id: 'tabs', icon: 'fa fa-window-restore', name: M.util.get_string('tabs', COMPONENTNAME)},
		{id: 'title', icon: 'fa fa-text-width', name: M.util.get_string('title', COMPONENTNAME)},
		{id: 'video', icon: 'fa fa-film', name: M.util.get_string('video', COMPONENTNAME)}	
	],
	
	
	

	CODES_ACCORDION = [		
		{
			id: 'accordion',
			name: M.util.get_string('accordion', COMPONENTNAME),			
			attribs: 
			[ 				
				{ 			
					name: 'parent',
					label: M.util.get_string('accordion_parent', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO1
				},
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				},
				{ 			
					type: 'repeat',
					repeat: 
					[
						{ 			
							parent: 'accordion_item',
							name: 'title',
							label: M.util.get_string('title', COMPONENTNAME),
							type: 'text',
							def: 'Title text...',
							desc: ''
						},
						{ 			
							parent: 'accordion_item',
							name: 'active',
							label: M.util.get_string('active', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: OPT_YESNO0
						},
						{ 			
							parent: 'accordion_item',
							name: 'icon',
							label: M.util.get_string('icon', COMPONENTNAME),
							type: 'icon',
							iconsfa: ICONSFA,  
							icons7s: ICONS7STROKE,
							def: '',
							desc: ''
						},
						{ 			
							parent: 'accordion_item',
							name: 'content',
							label: M.util.get_string('content', COMPONENTNAME),
							type: 'textarea',
							def: 'Content text...',
							desc: ''
						}
					]
				}				
			]
		}
	],
	CODES_BOXCONTENT = [		
		{
			id: 'boxes',
			name: M.util.get_string('boxcontent', COMPONENTNAME),			
			attribs: 
			[ 				
				{ 			
					name: 'columns',
					label: M.util.get_string('columns', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: '1'},
						{value: '2', title: '2'},
						{value: '3', title: '3'},
						{value: '4', title: '4'},
						{value: '5', title: '5'}
					]
				},
				{ 			
					type: 'repeat',
					repeat: 
					[												
						{ 			
							parent: 'boxcontent',
							name: 'type',
							label: M.util.get_string('type', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: 
							[
								{value: '', title: '1'},
								{value: '2', title: '2'},
								{value: '3', title: '3'},
								{value: '4', title: '4'},
								{value: '5', title: '5'}
							]
						},
						{ 			
							parent: 'boxcontent',
							name: 'color',
							label: M.util.get_string('color', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: 
							[
								{value: '', title: M.util.get_string('primary', COMPONENTNAME)},						
								{value: 'danger', title: M.util.get_string('danger', COMPONENTNAME)},
								{value: 'info', title: M.util.get_string('info', COMPONENTNAME)},
								{value: 'inverse', title: M.util.get_string('inverse', COMPONENTNAME)},								
								{value: 'success', title: M.util.get_string('success', COMPONENTNAME)},
								{value: 'warning', title: M.util.get_string('warning', COMPONENTNAME)},
								{value: 'gray', title: M.util.get_string('gray', COMPONENTNAME)}
							]
						},
						{ 			
							parent: 'boxcontent',
							name: 'title',
							label: M.util.get_string('title', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{ 			
							parent: 'boxcontent',
							name: 'text',
							label: M.util.get_string('text', COMPONENTNAME),
							type: 'textarea',
							def: '',
							desc: ''
						},
						{ 					
							parent: 'boxcontent',
							name: 'icon',
							label: M.util.get_string('icon', COMPONENTNAME),
							type: 'icon',
							iconsfa: ICONSFA,  
							icons7s: ICONS7STROKE,
							def: '',
							desc: ''
						},
						{ 			
							parent: 'boxcontent',
							name: 'link',
							label: M.util.get_string('link', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{ 			
							parent: 'boxcontent',
							name: 'linktext',
							label: M.util.get_string('linktext', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{
							parent: 'boxcontent',
							name: 'target',
							label: M.util.get_string('link_target', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: OPT_LINK_TARGET
						}
					]
				}				
			]
		}
	],
	CODES_BOXICON = [		
		{
			id: 'boxes',
			name: M.util.get_string('boxicon', COMPONENTNAME),			
			attribs: 
			[ 				
				{ 			
					name: 'columns',
					label: M.util.get_string('columns', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: '1'},
						{value: '2', title: '2'},
						{value: '3', title: '3'},
						{value: '4', title: '4'},
						{value: '5', title: '5'}
					]
				},
				{ 			
					type: 'repeat',
					repeat: 
					[
						{ 					
							parent: 'boxicon',
							name: 'icon',
							label: M.util.get_string('icon', COMPONENTNAME),
							type: 'icon',
							iconsfa: ICONSFA,  
							icons7s: ICONS7STROKE,
							def: '',
							desc: ''
						},						
						{ 			
							parent: 'boxicon',
							name: 'type',
							label: M.util.get_string('type', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: 
							[
								{value: '', title: '1'},
								{value: '2', title: '2'},
								{value: '3', title: '3'}
							]
						},
						{ 			
							parent: 'boxicon',
							name: 'color',
							label: M.util.get_string('color', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: 
							[
								{value: '', title: M.util.get_string('primary', COMPONENTNAME)},						
								{value: 'danger', title: M.util.get_string('danger', COMPONENTNAME)},
								{value: 'info', title: M.util.get_string('info', COMPONENTNAME)},
								{value: 'inverse', title: M.util.get_string('inverse', COMPONENTNAME)},								
								{value: 'success', title: M.util.get_string('success', COMPONENTNAME)},
								{value: 'warning', title: M.util.get_string('warning', COMPONENTNAME)},
								{value: 'gray', title: M.util.get_string('gray', COMPONENTNAME)}
							]
						},
						{ 			
							parent: 'boxicon',
							name: 'title',
							label: M.util.get_string('title', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{ 			
							parent: 'boxicon',
							name: 'text',
							label: M.util.get_string('text', COMPONENTNAME),
							type: 'textarea',
							def: '',
							desc: ''
						},
						{ 			
							parent: 'boxicon',
							name: 'link',
							label: M.util.get_string('link', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{
							parent: 'boxicon',
							name: 'target',
							label: M.util.get_string('link_target', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: OPT_LINK_TARGET
						}
					]
				}				
			]
		}
	],
	CODES_BOXIMG = [		
		{
			id: 'boxes',
			name: M.util.get_string('boximg', COMPONENTNAME),			
			attribs: 
			[ 				
				{ 			
					name: 'columns',
					label: M.util.get_string('columns', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: '1'},
						{value: '2', title: '2'},
						{value: '3', title: '3'},
						{value: '4', title: '4'}
					]
				},
				{ 			
					name: 'size',
					label: M.util.get_string('size', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('normal', COMPONENTNAME)},
						{value: 'small', title: M.util.get_string('small', COMPONENTNAME)}
					]
				},			
				{ 			
					type: 'repeat',
					repeat: 
					[
						{ 					
							parent: 'boximg',
							name: 'image',
							label: M.util.get_string('image', COMPONENTNAME),
							type: 'image',
							def: '',
							desc: ''
						},
						{ 			
							parent: 'boximg',
							name: 'text',
							label: M.util.get_string('text', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{ 			
							parent: 'boximg',
							name: 'link',
							label: M.util.get_string('link', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{
							parent: 'boximg',
							name: 'target',
							label: M.util.get_string('link_target', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: OPT_LINK_TARGET
						},
						{ 			
							parent: 'boximg',
							name: 'color',
							label: M.util.get_string('color', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: M.util.get_string('color_desc', COMPONENTNAME)
						}
					]
				}				
			]
		}
	],
	CODES_BUTTON = [
		{
			id: 'button',
			name: M.util.get_string('button', COMPONENTNAME),
			attribs: 
			[ 				
				{ 			
					name: 'text',
					label: M.util.get_string('text', COMPONENTNAME),
					type: 'text',
					def: 'Read more',
					desc: ''
				},
				{ 			
					name: 'link',
					label: M.util.get_string('link', COMPONENTNAME),
					type: 'text',
					def: '#',
					desc: ''
				},
				{
					name: 'target',
					label: M.util.get_string('link_target', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_LINK_TARGET
				},				
				{ 			
					name: 'type',
					label: M.util.get_string('type', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt:
					[
						{value: '', title: M.util.get_string('default', COMPONENTNAME)},						
						{value: 'danger', title: M.util.get_string('danger', COMPONENTNAME)},
						{value: 'info', title: M.util.get_string('info', COMPONENTNAME)},
						{value: 'inverse', title: M.util.get_string('inverse', COMPONENTNAME)},
						{value: 'primary', title: M.util.get_string('primary', COMPONENTNAME)},
						{value: 'success', title: M.util.get_string('success', COMPONENTNAME)},
						{value: 'warning', title: M.util.get_string('warning', COMPONENTNAME)},
						{value: 'default', title: M.util.get_string('default', COMPONENTNAME)},
						{value: 'link', title: M.util.get_string('link', COMPONENTNAME)}
					]
				},				
				{ 			
					name: 'size',
					label: M.util.get_string('size', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('normal', COMPONENTNAME)},
						{value: 'sm', title: M.util.get_string('small', COMPONENTNAME)},
						//{value: 'xs', title: M.util.get_string('xsmall', COMPONENTNAME)},
						{value: 'lg', title: M.util.get_string('large', COMPONENTNAME)}
						//{value: 'xl', title: M.util.get_string('xlarge', COMPONENTNAME)},
					]
				},
				{
					name: 'icon',
					label: M.util.get_string('icon', COMPONENTNAME),
					type: 'icon',
					iconsfa: ICONSFA,  
					icons7s: ICONS7STROKE,
					def: '',
					desc: ''
					
				},				
				{ 			
					name: 'border',
					label: M.util.get_string('button_border', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO0
				},
				{ 			
					name: 'fw',
					label: M.util.get_string('button_fw', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO0
				},
				/*{ 			
					name: 'rounded',
					label: M.util.get_string('button_rounded', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO1
				},*/				
				{ 			
					name: 'tttext',
					label: M.util.get_string('button_tttext', COMPONENTNAME),
					type: 'textarea',
					def: '',
					desc: ''
				},				
				{ 	
					name: 'ttpos',
					label: M.util.get_string('button_ttpos', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('top', COMPONENTNAME)},
						{value: 'right', title: M.util.get_string('right', COMPONENTNAME)},
						{value: 'bottom', title: M.util.get_string('bottom', COMPONENTNAME)},
						{value: 'left', title: M.util.get_string('left', COMPONENTNAME)},
					]
				},
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				}
				
			]
		}
	],
	CODES_COLUMNS = [		
		{
			id: 'columns',
			name: M.util.get_string('columns', COMPONENTNAME),			
			attribs: 
			[ 				
				{ 			
					type: 'repeat',
					repeat: 
					[
						{ 			
							parent: 'column',
							name: 'size',
							label: M.util.get_string('size', COMPONENTNAME),
							type: 'select',
							def: '4',
							desc: '',
							opt: 
							[
								{value: '4', title: M.util.get_string('column_13', COMPONENTNAME)},
								{value: '8', title: M.util.get_string('column_23', COMPONENTNAME)},
								{value: '6', title: M.util.get_string('column_12', COMPONENTNAME)},
								{value: '3', title: M.util.get_string('column_14', COMPONENTNAME)},
								{value: '9', title: M.util.get_string('column_34', COMPONENTNAME)}
							]
						},
						{ 			
							parent: 'column',
							name: 'content',
							label: M.util.get_string('content', COMPONENTNAME),
							type: 'textarea',
							def: 'Content text...',
							desc: ''
						}
					]
				}				
			]
		}
	],
	CODES_GAP = [
		{
			id: 'gap',
			name: M.util.get_string('gap', COMPONENTNAME),
			attribs: 
			[ 
				
				{ 					
					name: 'size',
					label: M.util.get_string('size', COMPONENTNAME),
					type: 'text',
					def: '20',
					desc: ''
				},
				{ 			
					name: 'smallscreen',
					label: M.util.get_string('gap_smallscreen', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO1
				}
				
			]
		}
	],	
	CODES_HEADER = [
		{
			id: 'header',
			name: M.util.get_string('header', COMPONENTNAME),
			attribs: 
			[ 
				
				{ 	
					name: 'type',
					label: M.util.get_string('type', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: 'dark', title: M.util.get_string('dark', COMPONENTNAME)},
						{value: 'dark-striped', title: M.util.get_string('dark_striped', COMPONENTNAME)},
						{value: 'light', title: M.util.get_string('light', COMPONENTNAME)},
						{value: 'light-striped', title: M.util.get_string('light_striped', COMPONENTNAME)}
					]
				},
				{ 	
					name: 'title',
					label: M.util.get_string('title', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 	
					name: 'subtitle',
					label: M.util.get_string('subtext', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 					
					name: 'image',
					label: M.util.get_string('bgimage', COMPONENTNAME),
					type: 'image',
					def: '',
					desc: ''
				},
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				}				
			]
		}
	],
	CODES_HEADINGS = [
		{
			id: 'h',
			name: M.util.get_string('headings', COMPONENTNAME),
			attribs: 
			[ 
				
				{ 	
					name: 'size',
					label: M.util.get_string('size', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '1', title: 'h1'},
						{value: '2', title: 'h2'},
						{value: '3', title: 'h3'},
						{value: '4', title: 'h4'},
						{value: '5', title: 'h5'},
						{value: '6', title: 'h6'}
					]
				},
				{ 	
					name: 'text',
					label: M.util.get_string('text', COMPONENTNAME),
					type: 'textarea',
					def: 'Heading text...',
					desc: ''
				},
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				}				
			]
		}
	],
	CODES_HIGHLIGHT = [
		{
			id: 'highlight',
			name: M.util.get_string('highlight', COMPONENTNAME),
			attribs: 
			[ 				
				{ 	
					name: 'type',
					label: M.util.get_string('type', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '1', title: '1'},
						{value: '2', title: '2'},
						{value: '3', title: '3'}
					]
				},
				{ 	
					name: 'text',
					label: M.util.get_string('text', COMPONENTNAME),
					type: 'textarea',
					def: '',
					desc: ''
				}			
			]
		}
	],
	CODES_ICON = [
		{
			id: 'icon',
			name: M.util.get_string('icon', COMPONENTNAME),
			attribs: 
			[ 
				{ 					
					name: 'name',
					label: M.util.get_string('name', COMPONENTNAME),
					type: 'icon',
					iconsfa: ICONSFA,  
					icons7s: ICONS7STROKE,
					def: 'fa-star',
					desc: ''
				},
				{ 	
					name: 'color',
					label: M.util.get_string('color', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('color_desc', COMPONENTNAME)
				},
				{ 	
					name: 'size',
					label: M.util.get_string('size', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('default', COMPONENTNAME)},
						{value: 's', title: M.util.get_string('small', COMPONENTNAME)},
						{value: 'l', title: M.util.get_string('large', COMPONENTNAME)},
						{value: 'xl', title: M.util.get_string('xlarge', COMPONENTNAME)},
					]
				},
				{ 	
					name: 'sizebg',
					label: M.util.get_string('icon_sizebg', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 	
					name: 'bgcolor',
					label: M.util.get_string('bgcolor', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('color_desc', COMPONENTNAME)
				},			
				{ 			
					name: 'spin',
					label: M.util.get_string('icon_spin', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO0
				},
				{ 			
					name: 'rotate',
					label: M.util.get_string('icon_rotate', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('no', COMPONENTNAME)},
						{value: 'rotate-90', title: M.util.get_string('icon_rotate_90', COMPONENTNAME)},
						{value: 'rotate-180', title: M.util.get_string('icon_rotate_180', COMPONENTNAME)},
						{value: 'rotate-270', title: M.util.get_string('icon_rotate_270', COMPONENTNAME)},
						{value: 'flip-horizontal', title: M.util.get_string('icon_rotate_fh', COMPONENTNAME)},
						{value: 'flip-vertical', title: M.util.get_string('icon_rotate_fv', COMPONENTNAME)}
					]
				},
				{ 			
					name: 'rounded',
					label: M.util.get_string('rounded', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO0
				},
				{ 	
					name: 'text',
					label: M.util.get_string('text', COMPONENTNAME),
					type: 'textarea',
					def: '',
					desc: ''
				},
				{ 			
					name: 'icon_text_pos',
					label: M.util.get_string('icon_text_pos', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('after', COMPONENTNAME)},
						{value: 'before', title: M.util.get_string('before', COMPONENTNAME)}
					]
				},
				{ 			
					name: 'nline',
					label: M.util.get_string('nline', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO0
				},
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				}							
			]
		}
	],	
	CODES_IMAGE = [
		{
			id: 'image',
			name: M.util.get_string('image', COMPONENTNAME),
			attribs: 
			[ 
				{ 					
					name: 'content',
					label: M.util.get_string('image', COMPONENTNAME),
					type: 'image',
					def: '',
					desc: ''
				},
				{ 	
					name: 'alt',
					label: M.util.get_string('alttext', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 					
					name: 'align',
					label: M.util.get_string('align', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('none', COMPONENTNAME)},
						{value: 'left', title: M.util.get_string('left', COMPONENTNAME)},
						{value: 'right', title: M.util.get_string('right', COMPONENTNAME)},
						{value: 'center', title: M.util.get_string('center', COMPONENTNAME)}						
					]
				},
				{ 					
					name: 'width',
					label: M.util.get_string('width', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 			
					name: 'link',
					label: M.util.get_string('link', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{
					name: 'link_target',
					label: M.util.get_string('link_target', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_LINK_TARGET						
				},
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				}
			]
		}
	],
	CODES_LINE = [
		{
			id: 'line',
			name: M.util.get_string('line', COMPONENTNAME),
			attribs: 
			[ 
				{ 					
					name: 'color',
					label: M.util.get_string('color', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('dark', COMPONENTNAME)},
						{value: 'light', title: M.util.get_string('light', COMPONENTNAME)}						
					]
				},
				{ 					
					name: 'custom_color',
					label: M.util.get_string('custom_color', COMPONENTNAME),
					type: 'color',
					def: '',
					desc: M.util.get_string('color_desc', COMPONENTNAME)
				},
				{ 					
					name: 'size',
					label: M.util.get_string('size', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 					
					name: 'style',
					label: M.util.get_string('style', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: 'solid'},
						{value: 'dotted', title: 'dotted'},
						{value: 'dashed', title: 'dashed'}						
					]
				},
				{ 			
					name: 'double',
					label: M.util.get_string('double', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO0
				},			
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				}
			]
		}
	],
	CODES_LIST = [		
		{
			id: 'list',
			name: M.util.get_string('list', COMPONENTNAME),			
			attribs: 
			[ 				
				{ 			
					name: 'type',
					label: M.util.get_string('type', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: '1'},
						{value: '2', title: '2'},
						{value: '3', title: '3'}						
					]
				},
				{ 					
					name: 'horizontal',
					label: M.util.get_string('horizontal', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO0
				},
				{ 					
					name: 'align',
					label: M.util.get_string('align', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('left', COMPONENTNAME)},
						{value: 'right', title: M.util.get_string('right', COMPONENTNAME)},
						{value: 'center', title: M.util.get_string('center', COMPONENTNAME)}						
					]
				},
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				},
				{ 			
					type: 'repeat',
					repeat: 
					[
						{ 			
							parent: 'list_item',
							name: 'text',
							label: M.util.get_string('text', COMPONENTNAME),
							type: 'text',
							def: 'List text...',
							desc: ''
						},
						{ 			
							parent: 'list_item',
							name: 'icon',
							label: M.util.get_string('icon', COMPONENTNAME),
							type: 'icon',
							iconsfa: ICONSFA,  
							icons7s: ICONS7STROKE,
							def: '',
							desc: ''
						},
						{ 			
							parent: 'list_item',
							name: 'link',
							label: M.util.get_string('link', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{
							parent: 'list_item',
							name: 'link_target',
							label: M.util.get_string('link_target', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: OPT_LINK_TARGET						
						}
					]
				}				
			]
		}
	],
	CODES_SLIDER = [		
		{
			id: 'slider',
			name: M.util.get_string('slider', COMPONENTNAME),			
			attribs: 
			[				
				{ 			
					name: 'columns',
					label: M.util.get_string('columns', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: '1'},
						{value: '2', title: '2'},
						{value: '3', title: '3'},
						{value: '4', title: '4'},
						{value: '5', title: '5'},
						{value: '6', title: '6'},
						{value: '7', title: '7'}						
					]
				},
				{ 					
					name: 'gutter',
					label: M.util.get_string('gutter', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},				
				{ 					
					name: 'autoplay',
					label: M.util.get_string('autoplay', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO1
				},
				{ 					
					name: 'loop',
					label: M.util.get_string('loop', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO1
				},
				{ 					
					name: 'pausetime',
					label: M.util.get_string('pausetime', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 					
					name: 'animtime',
					label: M.util.get_string('animtime', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 					
					name: 'nav',
					label: M.util.get_string('nav', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO1
				},
				{ 					
					name: 'dots',
					label: M.util.get_string('dots', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: OPT_YESNO1
				},
				{ 					
					name: 'width',
					label: M.util.get_string('width', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},				
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				},
				{ 			
					type: 'repeat',
					repeat: 
					[
						{ 			
							parent: 'slider_item',
							name: 'image',
							label: M.util.get_string('image', COMPONENTNAME),
							type: 'image',
							def: '',
							desc: ''
						},
						{ 			
							parent: 'slider_item',
							name: 'link',
							label: M.util.get_string('link', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{
							parent: 'slider_item',
							name: 'target',
							label: M.util.get_string('link_target', COMPONENTNAME),
							type: 'select',
							def: '',
							desc: '',
							opt: OPT_LINK_TARGET
						},
						{ 			
							parent: 'slider_item',
							name: 'title',
							label: M.util.get_string('title', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						},
						{ 			
							parent: 'slider_item',
							name: 'desc',
							label: M.util.get_string('description', COMPONENTNAME),
							type: 'text',
							def: '',
							desc: ''
						}
					]
				}				
			]
		}
	],
	CODES_TABS = [		
		{
			id: 'tabs',
			name: M.util.get_string('tabs', COMPONENTNAME),			
			attribs: 
			[ 				
				{ 					
					name: 'tabpos',
					label: M.util.get_string('tabs_pos', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('top', COMPONENTNAME)},
						{value: 'left', title: M.util.get_string('left', COMPONENTNAME)},
						{value: 'right', title: M.util.get_string('right', COMPONENTNAME)}						
					]
				},
				{ 					
					name: 'height',
					label: M.util.get_string('height', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},				
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				},
				{ 			
					type: 'repeat',
					repeat: 
					[
						{ 			
							parent: 'tab_item',
							name: 'title',
							label: M.util.get_string('title', COMPONENTNAME),
							type: 'text',
							def: 'Title text...',
							desc: ''
						},
						{ 			
							parent: 'tab_item',
							name: 'icon',
							label: M.util.get_string('icon', COMPONENTNAME),
							type: 'icon',
							iconsfa: ICONSFA,  
							icons7s: ICONS7STROKE,
							def: '',
							desc: ''
						},
						{ 			
							parent: 'tab_item',
							name: 'content',
							label: M.util.get_string('content', COMPONENTNAME),
							type: 'textarea',
							def: 'Content text...',
							desc: ''
						}
					]
				}				
			]
		}
	],
	CODES_TITLE = [
		{
			id: 'title',
			name: M.util.get_string('gap', COMPONENTNAME),
			attribs: 
			[ 				
				{ 					
					name: 'text',
					label: M.util.get_string('text', COMPONENTNAME),
					type: 'text',
					def: 'Title text...',
					desc: ''
				},
				{ 					
					name: 'subtext',
					label: M.util.get_string('subtext', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{
					name: 'style',
					label: M.util.get_string('style', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: '1'},
						{value: '2', title: '2'}						
					]
				},
				{
					name: 'size',
					label: M.util.get_string('size', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('default', COMPONENTNAME)},
						{value: 's', title: M.util.get_string('small', COMPONENTNAME)}						
					]
				},
				{ 					
					name: 'align',
					label: M.util.get_string('align', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('left', COMPONENTNAME)},
						{value: 'right', title: M.util.get_string('right', COMPONENTNAME)},
						{value: 'center', title: M.util.get_string('center', COMPONENTNAME)}						
					]
				},
				{ 	
					name: 'tag',
					label: M.util.get_string('htmltag', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: M.util.get_string('default', COMPONENTNAME)},
						{value: 'h1', title: 'h1'},
						{value: 'h2', title: 'h2'},
						{value: 'h3', title: 'h3'},
						{value: 'h4', title: 'h4'},
						{value: 'h5', title: 'h5'},
						{value: 'h6', title: 'h6'},
						{value: 'p', title: 'p'}
					]
				},
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				}				
			]
		}
	],
	CODES_VIDEO = [
		{
			id: 'video',
			name: M.util.get_string('video', COMPONENTNAME),
			attribs: 
			[ 
				
				{ 					
					name: 'id',
					label: M.util.get_string('video_id', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('video_id_desc', COMPONENTNAME)
				},
				{ 					
					name: 'bg_image',
					label: M.util.get_string('video_bg_image', COMPONENTNAME),
					type: 'image',
					def: '',
					desc: ''
				},
				{ 					
					name: 'width',
					label: M.util.get_string('width', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: ''
				},
				{ 	
					name: 'ratio',
					label: M.util.get_string('video_ratio', COMPONENTNAME),
					type: 'select',
					def: '',
					desc: '',
					opt: 
					[
						{value: '', title: '16:9'},
						{value: '4:3', title: '4:3'}
					]
				},				
				{ 					
					name: 'margin',
					label: M.util.get_string('margin', COMPONENTNAME),
					type: 'text',
					def: '',
					desc: M.util.get_string('margin_desc', COMPONENTNAME)
				}				
			]
		}
	],
	
	
	
	
	
	
	TEMPLATES = {
		
		
			
		CHOOSER : ''+
			'<div class="{{CSS.CODESELECTCONTAINER}}">'+
				'{{#each CODE_LIST}}' +                   
                    '<a class="{{../CSS.CODELINK}}" href="#" data-code="codes_{{this.id}}"><i class="{{this.icon}}"></i><span>{{this.name}}</span></a>'+                   
          		'{{/each}}' +
			'</div>',
		
		
		
		FORMS: '' +
			'<div class="{{CSS.CODECONTAINER}}">' +
                '{{#each CODES}}' +                    
					'<a href="#" class="{{../CSS.BACKTOCHOOSER}}">&larr; {{get_string "backtochooser" ../component}}</a>'+
					'<h4>{{this.name}}</h4>'+
					'<form class="{{../CSS.CODEFORM}} {{../CSS.CODEFORM}}_{{this.id}}" id="{{../CSS.CODEFORM}}_{{this.id}}" data-formid="{{this.id}}">' +
                    	'{{renderPartial "form_fields_list" context=../this attr=this.attribs}}'+
						'<input class="btn btn-default" type="submit" name="submit" value="{{get_string "generateshortcode" ../component}}" style="margin:20px 0;">' +					
                    '</form>' +
                '{{/each}}' +
				'<div class="{{CSS.CODEAREA}}">' +
				'</div>' +
				'<a href="#" class="{{CSS.INSERTBTN}} btn btn-success" style="margin-top:20px;">{{get_string "insertshortcode" component}}</a>'+				
    		'</div>',
			
		
		
		FORM_FIELDS_LIST: '' +
			'{{#each attr}}' +
				'{{#switch this.type}}' +
					'{{#case "text"}}{{renderPartial "form_elements.input_text" context=../../../this item=this}}{{/case}}' +
					'{{#case "icon"}}{{renderPartial "form_elements.input_icon" context=../../../this item=this iconsfa=this.iconsfa icons7s=this.icons7s}}{{/case}}' +	
					'{{#case "image"}}{{renderPartial "form_elements.input_image" context=../../../this item=this}}{{/case}}' +	
					'{{#case "color"}}{{renderPartial "form_elements.input_color" context=../../../this item=this}}{{/case}}' +	
					'{{#case "textarea"}}{{renderPartial "form_elements.input_textarea" context=../../../this item=this}}{{/case}}' +
					'{{#case "select"}}{{renderPartial "form_elements.input_select" context=../../../this item=this}}{{/case}}' +
					'{{#case "repeat"}}' +
						'<div class="{{../../../CSS.REPEATITEMS}}">' +
							'<div class="{{../../../CSS.REPEATITEMSINNER}}">' +
								'{{renderPartial "repeat_fields_list" context=../../../this attr=this.repeat}}' +
								'<a href="#" class="{{../../../CSS.REMOVEREPEAT}}">&times;</a>' +
							'</div>' +
							'<a class="{{../../../CSS.ADDREPEAT}}" href="#">{{get_string "newritem" ../../../component}}</a>' +
						'</div>' +
					'{{/case}}' +						
				'{{/switch}}' +
			'{{/each}}',
		
		
		
		REPEAT_FIELDS_LIST: '' +
			'{{#each attr}}' +
				'{{#switch this.type}}' +
					'{{#case "text"}}{{renderPartial "form_elements.input_text" context=../../../this item=this repeat=1}}{{/case}}' +	
					'{{#case "icon"}}{{renderPartial "form_elements.input_icon" context=../../../this item=this repeat=1 iconsfa=this.iconsfa icons7s=this.icons7s}}{{/case}}' +
					'{{#case "image"}}{{renderPartial "form_elements.input_image" context=../../../this item=this repeat=1}}{{/case}}' +
					'{{#case "color"}}{{renderPartial "form_elements.input_color" context=../../../this item=this repeat=1}}{{/case}}' +	
					'{{#case "textarea"}}{{renderPartial "form_elements.input_textarea" context=../../../this item=this repeat=1}}{{/case}}' +
					'{{#case "select"}}{{renderPartial "form_elements.input_select" context=../../../this item=this repeat=1}}{{/case}}' +					
				'{{/switch}}' +
			'{{/each}}',		
		
		
		
		FORM_ELEMENTS: {
			
			
			
			INPUT_TEXT: '' +
				'<div class="atto_mb2shortcodes_formfield">' +
					'<label>{{item.label}}</label>' +
					'<input type="text" name="{{#if repeat}}repeat[{{item.name}}:{{item.parent}}][]{{else}}{{item.name}}{{/if}}" value="{{item.def}}" />' +
					'{{#if item.desc}}'+
						'<span class="{{CSS.FORMFIELDDESC}}">{{item.desc}}</span>'+
					'{{/if}}'+
				'</div>',
			
			
			INPUT_COLOR: '' +
				'<div class="atto_mb2shortcodes_formfield">' +
					'<label>{{item.label}}</label>' +
					'<input class="mb2color" type="text" name="{{#if repeat}}repeat[{{item.name}}:{{item.parent}}][]{{else}}{{item.name}}{{/if}}" value="{{item.def}}" />' +
					'{{#if item.desc}}'+
						'<span class="{{CSS.FORMFIELDDESC}}">{{item.desc}}</span>'+
					'{{/if}}'+
				'</div>',
			
			
			
			INPUT_ICON: '' +
				'<div class="atto_mb2shortcodes_formfield">' +
					'<label>{{item.label}}</label>' +
					'<input class="{{CSS.ICONINPUT}}" type="hidden" name="{{#if repeat}}repeat[{{item.name}}:{{item.parent}}][]{{else}}{{item.name}}{{/if}}" value="{{item.def}}" />' +
					'<span class="{{CSS.ICONPREVIEW}}"><i style="display:none;"></i></span>'+
					'<a href="#" class="{{CSS.ADDICON}}">{{get_string "addicon" component}}</a>' +
					'<a href="#" class="{{CSS.REMOVEICON}} {{CSS.BTNSMALLDANGER}}">&times;</a>' +
					'{{renderPartial "form_elements.input_icon_tab" context=this}}'+
					'<div class="{{CSS.ICONTABSAREA}}"></div>'+
				'</div>',
				
			
			INPUT_TEXTAREA: '' +
				'<div class="atto_mb2shortcodes_formfield">' +
					'<label>{{item.label}}</label>' +
					'<textarea name="{{#if repeat}}repeat[{{item.name}}:{{item.parent}}][]{{else}}{{item.name}}{{/if}}">{{item.def}}</textarea>' +
					'{{#if item.desc}}'+
						'<span class="{{CSS.FORMFIELDDESC}}">{{item.desc}}</span>'+
					'{{/if}}'+
				'</div>',
				
				
			INPUT_SELECT: '' +
				'<div class="atto_mb2shortcodes_formfield">' +
					'<label>{{item.label}}</label>' +
						'<select name="{{#if repeat}}repeat[{{item.name}}:{{item.parent}}][]{{else}}{{item.name}}{{/if}}">' +
						'{{renderPartial "form_elements.input_select_opt" context=this opts=item.opt}}' +
					'</select>' +
					'{{#if item.desc}}'+
						'<span class="{{CSS.FORMFIELDDESC}}">{{item.desc}}</span>'+
					'{{/if}}'+
				'</div>',
				
				
			INPUT_IMAGE: '' +
				'<div class="atto_mb2shortcodes_formfield">' +
					'<label>{{item.label}}</label>' +					
					'{{#if showFilepicker}}' +
						'<input type="hidden" name="{{#if repeat}}repeat[{{item.name}}:{{item.parent}}][]{{else}}{{item.name}}{{/if}}" class="{{CSS.IMAGEINPUTURL}}" value="{{item.def}}" />' +
						'<img src="" class="{{CSS.IMAGEPREVIEW}}" alt="" style="width:100px;height:auto;display:none;" />'+
						'<button class="{{CSS.IMAGEBROWSER}}" type="button">{{get_string "addimage" component}}</button>' +
						'<a href="#" class="{{CSS.IMAGECLEAR}} {{CSS.BTNSMALLDANGER}}">&times;</a>' +
					'{{else}}'+					
						'<input type="text" name="{{#if repeat}}repeat[{{item.name}}:{{item.parent}}][]{{else}}{{item.name}}{{/if}}" class="{{CSS.IMAGEINPUTURL}}" value="{{item.def}}" />' +
					'{{/if}}'+
				'</div>',
				
				
			INPUT_SELECT_OPT: '' +
				'{{#each opts}}' +
					'<option value="{{this.value}}">{{this.title}}</option>' +
				'{{/each}}',
				
				
				
			INPUT_ICON_TAB: '' +
				'<div class="{{CSS.ICONTABS}}" style="display:none;">' +
					'<ul class="{{CSS.ICONTABSLIST}}">' +
						'<li><a href="#icons_fa">Font Awesome</a></li>' +
						'<li><a href="#icons_7stroke">7 Stroke</a></li>' +
					'</ul>' +
					'<div class="{{CSS.ICONTABSCONTENT}}">' +
						'<div class="{{CSS.ICONTABSCONTENTITEM}}" id="icons_fa">' +
							'{{#each iconsfa}}' +
								'<a class="{{../CSS.ICONITEM}}" href="#" data-icon="{{this.name}}" title="{{this.name}}"><i class="fa {{this.name}}"></i></a>' +
							'{{/each}}' +
						'</div>' +
						'<div class="{{CSS.ICONTABSCONTENTITEM}}" id="icons_7stroke">' +
							'{{#each icons7s}}' +
								'<a class="{{../CSS.ICONITEM}}" href="#" data-icon="{{this.name}}" title="{{this.name}}"><i class="{{this.name}}"></i></a>' +
							'{{/each}}' +
						'</div>' +
					'</div>' +
				'</div>',
				
							
				
		}
			
	};


	



Y.namespace('M.atto_mb2shortcodes').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    
	/**
     * The most recently selected image.
     *
     * @param _selectedImage
     * @type Node
     * @private
     */
    _selectedImage: null,
    _currentSelection: null,
	_content: null,
   
   
    initializer: function() {
        this.addButton({
            icon: 'e/document_properties',
            callback: this._displayDialogueCode
        });
		
				
		
    },

   
  
  
  
	
	
    _displayDialogueCode: function() {
        
		
		this._currentSelection = this.get('host').getSelection();
       
	   
	    if (this._currentSelection === false) {
            return;
        }
		
		
		if (!('switch' in Y.Handlebars.helpers)) 
		{
		
			Y.Handlebars.registerHelper("switch", function(value, options) {
				this._switch_value_ = value;
				var html = options.fn(this); // Process the body of the switch block
				delete this._switch_value_;
				return html;
			});
			
			
			Y.Handlebars.registerHelper("case", function(value, options) {
				if (value == this._switch_value_) {
					return options.fn(this);
				}
			});

		}
		
		
		
		 if (!('renderPartial' in Y.Handlebars.helpers)) {
            (function smashPartials(chain, obj) {
                Y.each(obj, function(value, index) {
                    chain.push(index);
                    if (typeof value !== "object") {
                        Y.Handlebars.registerPartial(chain.join('.').toLowerCase(), value);
                    } else {
                        smashPartials(chain, value);
                    }
                    chain.pop();
                });
            })([], TEMPLATES);

            Y.Handlebars.registerHelper('renderPartial', function(partialName, options) {
                if (!partialName) {
                    return '';
                }

                var partial = Y.Handlebars.partials[partialName];
                var parentContext = options.hash.context ? Y.clone(options.hash.context) : {};
                var context = Y.merge(parentContext, options.hash);
                delete context.context;

                if (!partial) {
                    return '';
                }
                return new Y.Handlebars.SafeString(Y.Handlebars.compile(partial)(context));
            });
        }
		
		
				
		

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('pluginname', COMPONENTNAME),
			width: '600px',
        }, true);		
		
		
		
        
        dialogue.set('bodyContent', this._getDialogueChooser()).show();
		
    },
	
	
	
	
	_getDialogueChooser: function() {
		
		var isThis = this;
		
		 var dialogue = isThis.getDialogue({
            headerContent: M.util.get_string('pluginname', COMPONENTNAME),
			width: '600px',
        }, true);
		
		
		
		var template = Y.Handlebars.compile(TEMPLATES.CHOOSER);
	 	

        var content = Y.Node.create(template({
            component: COMPONENTNAME,			
            CSS: CSS,
			SELECTORS: SELECTORS,
			CODE_LIST: CODE_LIST
        }));
		
						
		
		content.all(SELECTORS.CODELINK).on('click', function(e){
			
			e.preventDefault();
			
			var codeString = e.currentTarget.getData('code').toUpperCase();			
			var codeObj = eval(codeString);
			
			dialogue.set('bodyContent', isThis._getDialogueCode(codeObj)).show();
			
		}, isThis);
		
		
		
		
		return content;
		
		
	},
	
	
	
	
	
	
    _getDialogueCode: function(code) {
      
		
		var isThis = this;
		
		var dialogue = isThis.getDialogue({
            headerContent: M.util.get_string('pluginname', COMPONENTNAME),
			width: '600px',
        }, true);
		
		
		
		
		
				
	
		var template = Y.Handlebars.compile(TEMPLATES.FORMS), 	 	
		host = isThis.get('host'),
		canShowFilepicker = isThis.get('host').canShowFilepicker('image');
		

        var content = Y.Node.create(template({
            component: COMPONENTNAME,			
            CSS: CSS,
			SELECTORS: SELECTORS,
			CODES: code,
			showFilepicker: canShowFilepicker
        }));
			   
			
			
		
				
		content.all(SELECTORS.CODEFORM).on('submit',isThis._ajaxShortcode, isThis);
		content.one(SELECTORS.INSERTBTN).on('click', isThis._insertChar, isThis);
		
		
		
		
		
		
		
		
		var repeatBtn = content.one(SELECTORS.ADDREPEAT);
		
		if (repeatBtn)
		{
			repeatBtn.on('click', function(e){
				
				e.preventDefault();			
				
				var rpItems = e.target.ancestor(SELECTORS.REPEATITEMS);			
				var firstrow = e.target.ancestor(SELECTORS.REPEATITEMS).one(SELECTORS.REPEATITEMSINNER);
				var newrow = firstrow.cloneNode(true);			
			
				rpItems.append(newrow);			
				
			}, this);
		
		}
		
		
		
		if (canShowFilepicker) 
		{ 
		   
		   content.all(SELECTORS.CODEFORM).each(function(el) {			
			 
				 el.delegate('click', function(e){					
									
					//var element = e.currentTarget;					
					host.showFilepicker('image', isThis._mb2FilepickerCallback(e), isThis);					
				
				}, SELECTORS.IMAGEBROWSER, isThis);			
				
				 el.delegate('click', isThis._fileClear ,SELECTORS.IMAGECLEAR, isThis);			
				 
			});		   
		   
        }	
		
		
		content.all(SELECTORS.CODEFORM).each(function(el) {			
			 
			el.delegate('click', function(e){
				
				e.preventDefault();
				var parent = e.currentTarget.ancestor(SELECTORS.REPEATITEMSINNER);
				
				parent.remove();
				
			} , SELECTORS.REMOVEREPEAT, isThis);		
				 
		});	
		
		
		
		
		
		content.all(SELECTORS.CODEFORM).each(function(el) {			
			 
			
			
			el.delegate('click', function(e){
				
				e.preventDefault();
				var element = e.currentTarget;
				
				var tabs = element.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.ICONTABS);
				var tabsArea = element.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.ICONTABSAREA);
				
				
				tabs.show();
			
				var tabview = new Y.TabView({
					srcNode: tabs
				});
		
			   tabview.render(tabsArea);
				
				
				
			} , SELECTORS.ADDICON, isThis);
			
			
			
			
			
			// Set icon
			el.delegate('click', function(e){
				
				e.preventDefault();
				
				
				var element = e.currentTarget;
				var iconName = element.getData('icon');
				
				var iconPref = '';
				var iconReg = /fa-/;
				var match = iconReg.exec(iconName);
				
				if (match)
				{
					iconPref = 'fa ';	
				}
				
				
				
				var iconInput = element.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.ICONINPUT);	
				var tabs = element.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.ICONTABS);				
				var iconPrev = element.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.ICONPREVIEW);
				
						
				iconInput.set('value', iconName);
				iconPrev.show();
				iconPrev.removeAttribute('class');
				iconPrev.addClass(iconPref+iconName);
				tabs.hide();
				
				
			}, SELECTORS.ICONITEM, isThis);
			
			
			
			
			// Clear icon
			el.delegate('click', function(e){
				
				e.preventDefault();
				
				var element = e.currentTarget;
				
				var tabs = element.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.ICONTABS);
				var iconPrev = element.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.ICONPREVIEW);
				
				tabs.hide();
				iconPrev.removeAttribute('class');
				iconPrev.hide();
				
				
			}, SELECTORS.REMOVEICON, isThis);
			
			
			
			
			
			
			
			
			
					
				 
		});	
		
		
		
		
		content.one(SELECTORS.BACKTOCHOOSER).on('click', function(e){
			
			e.preventDefault();			
			
			dialogue.set('bodyContent', isThis._getDialogueChooser()).show();
			
			
		}, isThis);
		
		
			
		return content;
	
    },
	
	
	
	
	
	
	
	
	_fileClear: function(e)
	{
		
		e.preventDefault();
		
		var imgUrl = e.currentTarget.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.IMAGEINPUTURL);
		var imgPrev = e.currentTarget.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.IMAGEPREVIEW);
		
		
		imgUrl.set('value', '');
		imgPrev.hide();
		imgPrev.set('src', '');
		
		
		
	},
	
	
	 _mb2FilepickerCallback: function(e) 
	 {
       
	
		return function(params) {
			
			
			if (params.url !== '') 
			{
				var imgUrl = e.currentTarget.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.IMAGEINPUTURL);
				var imgPrev = e.currentTarget.ancestor(SELECTORS.FORMFIELD).one(SELECTORS.IMAGEPREVIEW);
				
				imgUrl.set('value', params.url);
				imgPrev.show();
				imgPrev.set('src', params.url);				
				
			}			
			
		}	
	
		
    },
	
	
	
	
	
	
	
	
	
	_addRemoveRepeatItem: function(e) {
		
		
		e.preventDefault();
		var rpItems = $(SELECTORS.REPEATITEMS);
		var clonedItems = $(SELECTORS.REPEATITEMSINNER).one().clone();
		
		
		rpItems.append(clonedItems);
		
		
		
		
		
	},
	
	
	
	
	
	
	
	
	_ajaxShortcode: function (e) {
				
		e.preventDefault();
		
	
		var formId = e.target.get('value');
		var context = this, args = arguments;	
		var formId = e.target.getData('formid');
		var formSelector = $(SELECTORS.CODEFORM + '_' + formId);
		
		
		url = M.cfg.wwwroot + '/lib/editor/atto/plugins/mb2shortcodes/ajax.php';
       
	  	params = {
    		sesskey: M.cfg.sesskey,
            contextid: this.get('contextid'),
            shortcode: formId,
            text: formSelector.serialize()
        };

       
	   Y.io(url, {
            context: this,
            data: params,
			method: 'POST',
			on: {
				
				start: function(x,o)
				{
					
					$(SELECTORS.CODEAREA).addClass('waiting');
					
				},
				success: function(x,o)
				{
					
					$(SELECTORS.CODEAREA).html('');
					$(SELECTORS.CODEAREA).removeClass('waiting');
					$(SELECTORS.CODEAREA).append(o.responseText);
					
				},
				failure: function(x,o)
				{
					
					$(SELECTORS.CODEAREA).append(M.util.get_string('ajaxfailure', COMPONENTNAME));
					
				}
			}
			
        });
		
		
		
	},
	
	
	
    _insertChar: function(e) {
        
		e.preventDefault();
        var character = $(SELECTORS.CODEAREA).html();

        // Hide the dialogue.
        this.getDialogue({
            focusAfterHide: null
        }).hide();

        var host = this.get('host');

        // Focus on the last point.
        host.setSelection(this._currentSelection);

        // And add the character.
		if (character)
		{
        host.insertContentAtFocusPoint(character);
		}

        // And mark the text area as updated.
        this.markUpdated();
    }
}, {
    ATTRS: {
       

        /**
         * The contextid to use when generating this preview.
         *
         * @attribute contextid
         * @type String
         */
        contextid: {
            value: null
        }

    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin",
        "moodle-core-event",
        "io",
        "event-valuechange",
        "tabview",
        "array-extras"]});
