M.block_heatmap = {
    config: 0,
    min: 0,
    max: 0,
    diff: 0,
    numColours: 5,
    toggleState: true,
    viewsicon: '',
    usersicon: '',

    initHeatmap: function (YUIObject, config, min, max, toggledon, viewsicon, usersicon, whattoshow) {
        this.config = JSON.parse(config);
        this.min = min;
        this.max = max;
        this.diff = max - min + 1;
        this.toggleState = toggledon == 1;
        this.viewsicon = viewsicon;
        this.usersicon = usersicon;
        this.whattoshow = whattoshow;
        if (this.toggleState) {
            this.showHeatmap();
        }
    },

    showHeatmap: function () {
        var module;
        var weight;
        var info;
        for (var i = 0; i < this.config.length; i++) {
            module = document.getElementById('module-' + this.config[i].cmid);
            weight = parseInt((this.config[i].numviews - this.min) / this.diff * this.numColours);
            if (module) {
                if (this.whattoshow != 'showicons') {
                    module.className += ' block_heatmap_heat_' + weight;
                }
                if (this.whattoshow != 'showbackground') {
                    info = '<div class="block_heatmap_view_count">';
                    info += this.viewsicon;
                    info += '&nbsp;<span class="block_heatmap_views block_heatmap_icon_' + weight + '">';
                    info += this.config[i].numviews;
                    info += '</span> &nbsp;';
                    info += this.usersicon;
                    info += '&nbsp;<span class="block_heatmap_users block_heatmap_icon_' + weight + '"">';
                    info += this.config[i].distinctusers;
                    info += '</span></div>';
                    module.innerHTML = module.innerHTML + info;
                }
            }
        }
    },

    hideHeatmap: function () {
        var module;
        for (var i = 0; i < this.config.length; i++) {
            module = document.getElementById('module-' + this.config[i].cmid);
            if (module) {
                module.className = module.className.replace(/ block_heatmap_heat_(\d)/, '');
                module.removeChild(module.getElementsByClassName('block_heatmap_view_count')[0]);
            }
        }
    },

    toggleHeatmap: function () {
        this.toggleState = !this.toggleState;
        if(this.toggleState) {
            this.showHeatmap();
        }
        else {
            this.hideHeatmap();
        }
        M.util.set_user_preference('heatmaptogglestate', this.toggleState);
    }
};