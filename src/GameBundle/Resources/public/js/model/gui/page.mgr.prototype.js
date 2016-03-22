'use strict';

/**
 * @constructor
 */
function PageMgr() {
    this.modalMgr = new ModalMgr();
    this.alertMgr = new AlertMgr();
    this.$docTitle = $('head>title');
    this.$loading = $('.page-loading');
    this.$sidebar = $('.page-sidebar');
    this.$content = $('.page-content');
    this.$pageTitle = this.$content.find('.page-section-title');
}

/**
 * @property {ModalMgr} modalMgr
 * @property {AlertMgr} alertMgr
 *
 * @property {jQuery}   $docTitle
 * @property {jQuery}   $loading
 * @property {jQuery}   $sidebar
 * @property {jQuery}   $content
 * @property {jQuery}   $pageTitle
 */
PageMgr.prototype = {
    /**
     * @returns {PageMgr}
     */
    toggleSidebar: function () {
        let css = PageMgr.resources.config.trigger.css;

        this.$sidebar.toggleClass(css.toggle);
        this.$content.toggleClass(css.toggle);

        return this;
    },
    /**
     * @param {Element} el
     *
     * @returns {PageMgr}
     */
    switchSection: function (el) {
        let config = PageMgr.resources.config;

        this.toggleTitle(el);
        this.hideAll();
        this.alertMgr.hide();

        switch (el.getAttribute(config.trigger.action)) {
            case config.action.game.new:
                break;
            default:
                let section = el.getAttribute(config.trigger.section);
                switch (section) {
                    case config.section.game.current:
                    case config.section.game.results:
                        this.show(section);
                        break;
                }
                break;
        }

        return this;
    },
    /**
     * @returns {PageMgr}
     */
    hideAll: function () {
        let css = PageMgr.resources.config.trigger.css;

        this.$content.find('.container-fluid>.row>div:not(#notification-area)').addClass(css.hidden);
        this.$sidebar.find('li:not(.sidebar-brand)').removeClass(css.selected);

        return this;
    },
    /**
     * @param {string} id
     *
     * @returns {PageMgr}
     */
    show: function (id) {
        let css = PageMgr.resources.config.trigger.css;

        this.$content.find('div#' + id).removeClass(css.hidden);
        this.$sidebar.find('li[data-section="' + id + '"]').addClass(css.selected);

        return this;
    },
    /**
     * @param {Element} el
     *
     * @returns {PageMgr}
     */
    toggleTitle: function (el) {
        let postfix = el.innerText,
            prefix = this.$sidebar.find('.' + PageMgr.resources.config.trigger.css.title).text();

        this.$docTitle.text(prefix + ' :: ' + postfix);
        this.$pageTitle.text(postfix);

        return this;
    },
    /**
     * @param {boolean} enable
     *
     * @returns {PageMgr}
     */
    loadingMode: function (enable) {
        let css = PageMgr.resources.config.trigger.css;

        this.modalMgr.updateHTML('');

        if (enable) {
            this.$loading.removeClass(css.hidden);
            this.modalMgr.show();
        } else {
            this.$loading.addClass(css.hidden);
            this.modalMgr.hide();
        }

        return this;
    }
};

PageMgr.resources = {};
PageMgr.resources.config = {
    action: {
        /** @enum {string} */
        game: {
            new: 'game-new-action'
        }
    },
    section: {
        /** @enum {string} */
        game: {
            current: 'game-current-area',
            results: 'game-results-area'
        }
    },
    trigger: {
        /** @enum {string} */
        css: {
            selected: 'selected',
            title: 'page-header',
            toggle: 'toggled',
            hidden: 'hidden'
        },

        /** @type {string} */
        action: 'data-action',
        /** @type {string} */
        section: 'data-section'
    }
};