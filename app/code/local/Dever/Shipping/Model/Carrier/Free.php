<?php
/**
 * Created by PhpStorm.
 * User: prabu
 * Date: 09/07/17
 * Time: 3:52 PM
 */
class Dever_Shipping_Model_Carrier_Free
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'custom_shipping_free';

    public function getAllowedMethods()
    {
        return array('custom_shipping_free' => $this->getConfigData('name'));
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if(!Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeBoxes += $item->getQty() * $child->getQty();
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        $this->setFreeBoxes($freeBoxes);

        $result = Mage::getModel('shipping/rate_result');
        //$shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);
        $shippingPrice = 0;

        if ($shippingPrice !== false) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('custom_shipping_free');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('custom_shipping_free');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }

        return $result;
    }
}