/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Shopware_Paypal
 * @subpackage Paypal
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

//{namespace name=backend/payment_paypal/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/config/view/main/window"}
Ext.define('Shopware.apps.PaymentPaypal.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.paypal-main-window',

    width: 1200,
    height: 500,
    layout: 'border',

    title: '{s name=window/title}PayPal Payments{/s}',

    /**
     *
     */
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        me.callParent(arguments);
    },

    /**
     * @return array
     */
    getItems: function() {
        var me = this;
        return [{
            region: 'east',
            xtype: 'paypal-main-detail'
        }, {
            region: 'center',
            xtype: 'paypal-main-list'
        }];
    }
});
//{/block}