'use strict';

/**
 * @param {jQuery} $el
 *
 * @constructor
 */
function Game($el) {
    this.$html = $el;

    this.apiMgr = new APIRequestMgr();
    this.alertMgr = new AlertMgr();
    this.pageMgr = new PageMgr();
    this.modalMgr = new ModalMgr();
}

/**
 * @property {APIRequestMgr} apiMgr
 * @property {AlertMgr}      alertMgr
 * @property {PageMgr}       pageMgr
 * @property {ModalMgr}      modalMgr
 *
 * @property {jQuery}        $html
 *
 * @property {int}           id
 * @property {Player[]}      players
 */
Game.prototype = {
    /**
     * @param {int|string} id
     *
     * @returns {Game}
     */
    setId: function (id) {
        this.id = id;

        return this;
    },
    /**
     * @returns {{id: {int}}}
     */
    getJSON: function () {
        return {id: this.id};
    },
    /**
     * @param {{name: {string}, isCPU: {boolean}}[]} players
     * @param {int}                                  battlefieldSize
     */
    init: function (players, battlefieldSize) {
        this.pageMgr.switchSection(document.querySelector('.page-sidebar li[data-section="game-current-area"]'));

        this.setId('undefined');
        this.players = [];
        this.$html.html('');

        let self = this,
            requestData = {
                game: this.getJSON(),
                data: []
            },
            onSuccess = function (response) {
                self.parseInitResponse(response);
            };

        players.map(function (_player) {
            let player = new Player(_player.name, _player.isCPU || false, battlefieldSize);

            self.players.push(player);
            self.$html.append(player.$html);

            requestData.data.push({
                player: player.getJSON(),
                battlefield: player.battlefield.getJSON(),
                cells: player.battlefield.cellContainer.getJSON()
            });
        });

        this.apiMgr.request('POST', this.$html.attr(Game.resources.config.route.init), requestData, onSuccess);
    },
    /**
     * @param {{
     *          id: {int},
     *          battlefields: {
     *              id: {int},
     *              player: {id: {int}, name: {string}},
     *              cells: {id: {int}, x: {int}, y: {int}, state: {id: {int}}}
     *          }[]
     *        }} response
     */
    parseInitResponse: function (response) {
        this.setId(response.id);
        let self = this;

        response.battlefields.map(function (el) {
            let player = self.findPlayerByName(el.player.name);

            if (undefined !== player) {
                player.setId(el.player.id);

                el.cells.forEach(function (el) {
                    let cell = self.findCell({playerId: player.id, x: el.x, y: el.y});

                    if (undefined !== cell) {
                        cell.setId(el.id);
                    }
                });
            }
        });
    },
    /**
     * @param {Element} el
     */
    update: function (el) {
        let cell = this.findCell({id: el.getAttribute('data-cell-id')});
        if (undefined !== cell) {
            this.cellSend(cell.getJSON());
        }
    },
    /**
     * @param id {int}
     *
     * @returns {Player|undefined}
     */
    findPlayerById: function (id) {
        return this.players.find(player => player.id == id);
    },
    /**
     * @param name {string}
     *
     * @returns {Player|undefined}
     */
    findPlayerByName: function (name) {
        return this.players.find(player => player.name == name);
    },
    /**
     * @param {{cell: {Object}}} requestData
     */
    cellSend: function (requestData) {
        var self = this,
            onSuccess = function (response) {
                self.parseUpdateResponse(response);
            };

        this.apiMgr.request('PATCH', this.$html.attr(Game.resources.config.route.turn), requestData, onSuccess);
    },
    /**
     * @param {{cells: {id: {int}, state: {id: {int}}}[], result: {player: {Object}}}} response
     */
    parseUpdateResponse: function (response) {
        let self = this;

        response.cells.forEach(function (_cell) {
            let cell = self.findCell({id: _cell.id});

            if (undefined !== cell) {
                cell.setState(_cell.state.id);
            }
        });

        if (undefined !== response.result) {
            let text = Game.resources.config.text,
                type = AlertMgr.resources.config.type,
                player = this.findPlayerById(response.result.player.id);

            if (undefined !== player) {
                player.isHuman()
                    ? this.alertMgr.show(text.win, type.success)
                    : this.alertMgr.show(text.loss, type.error);
            }
        }
    },
    /**
     * @param {{playerId: {int}, id: {int}, x: {int}, y: {int}}} criteria
     *
     * @returns {Cell}
     */
    findCell: function (criteria) {
        for (let i = 0; i < this.players.length; i++) {
            if (undefined !== criteria.playerId && criteria.playerId !== this.players[i].id) {
                continue;
            }

            let cell = this.players[i].battlefield.findCell(criteria);

            if (undefined !== cell) {
                return cell;
            }
        }
    },
    modalGameInitiation: function () {
        this.alertMgr.hide();
        this.modalMgr.updateHTML(Game.resources.html.modal).show();

        return this;
    },
    /**
     * @param {Element} el
     *
     * @returns {boolean}
     */
    modalValidateInput: function (el) {
        let config = Game.resources.config,
            battlefieldSize = config.pattern.battlefield;

        switch (el.id) {
            case config.trigger.player:
                if (!config.pattern.username.test(el.value)) {
                    el.value = el.value.substr(0, el.value.length - 1);

                    return false;
                }
                return true;
            case config.trigger.bfsize:
                if (isNaN(el.value))
                    el.value = el.value.substr(0, el.value.length - 1);
                else if (el.value.length > 1 && el.value < battlefieldSize.min)
                    el.value = battlefieldSize.min;
                else if (el.value.length > 2 || el.value > battlefieldSize.max)
                    el.value = battlefieldSize.max;

                return battlefieldSize.min >= el.value <= battlefieldSize.max;
        }
    },
    modalUnlockSubmission: function () {
        this.modalMgr.unlockSubmission(false);

        let trigger = Game.resources.config.trigger,
            isUsernameValid = this.modalValidateInput(document.getElementById('model-trigger-username')),
            isBattlefieldSizeValid = this.modalValidateInput(document.getElementById('model-trigger-battlefield-size'));

        if (isUsernameValid && isBattlefieldSizeValid) {
            this.modalMgr.unlockSubmission(true);
        }
    }
};

Game.resources = {};
Game.resources.config = {
    /** @enum {string} */
    trigger: {
        username: 'model-trigger-username',
        game_size: 'model-trigger-battlefield-size'
    },
    /** @enum {string} */
    text: {
        win: 'you won',
        loss: 'you lost'
    },
    pattern: {
        /** @enum {int} */
        battlefield: {
            min: 5,
            max: 15
        },
        /** @type {Object} */
        username: /^[a-zA-Z0-9\.\-\ \@]{1,100}$/
    },
    /** @enum {string} */
    route: {
        turn: 'data-turn-link',
        init: 'data-init-link'
    }
};
Game.resources.html = {
    /**
     * @returns {string}
     */
    modal: function () {
        let battlefield = Game.resources.config.pattern.battlefield;

        return '' +
            '<div class="modal fade">' +
                '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<button type="button" class="close" data-dismiss="modal">' +
                                '<span aria-hidden="true">&times;</span>' +
                            '</button>' +
                            '<h4 class="modal-title">your details</h4>' +
                        '</div>' +
                        '<div class="modal-body">' +
                            '<div class="form-group">' +
                                '<label for="model-trigger-username">nickname</label>' +
                                '<input type="text" class="form-control" id="model-trigger-username" placeholder="">' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<label for="model-trigger-battlefield-size">battlefield size</label>' +
                                '<input type="test" class="form-control" id="model-trigger-battlefield-size"' +
                                    ' placeholder="between ' + battlefield.min + ' and ' + battlefield.max + '">' +
                            '</div>' +
                        '</div>' +
                        '<div class="modal-footer">' +
                            '<button type="button" id="new-game-btn" class="btn btn-primary" disabled="disabled">next step</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }
};