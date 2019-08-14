<?php
/**
 * Experian Qas Autocompletion block - used for Intuitive Engine
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Block_Autocomplete extends Mage_Core_Block_Abstract
{
    protected $_suggestData = null;

    /**
     * Return html result
     *
     * @return string html result
     */
    protected function _toHtml()
    {
        $html = '';

        if (!$this->_beforeToHtml()) {
            return $html;
        }

        $suggestData = $this->getSuggestData();
        if (!($count = count($suggestData))) {
            return $html;
        }

        $count--;

        $html = '<ul><li style="display:none"></li>';
        foreach ($suggestData as $index => $item) {
            if ($index == 0) {
                $item['row_class'] .= ' first';
            }

            if ($index == $count) {
                $item['row_class'] .= ' last';
            }

            $html .=  '<li moniker="'.$this->htmlEscape($item['value']).'" title="'.$this->htmlEscape($item['title']).'" class="'.$item['row_class'].'">'
                . $this->htmlEscape($item['title']).'</li>';
        }

        $html.= '</ul>';

        return $html;
    }

    /**
     * Format the addresses response for Intuitive Engine
     *
     * @return array
     */
    protected function getSuggestData()
    {
        if (is_null($this->_suggestData)) {

            $picklistEntries = $this->getPicklistEntries();
            $response = array();
            $data = array();
            $counter = 0;

            if (is_array($picklistEntries)) {
                foreach ($picklistEntries as $entry){
                    $_data = array(
                        'value' => $entry->Moniker,
                        'title' => $entry->PartialAddress,
                        'row_class' => (++$counter)%2?'odd':'even'
                    );
                    array_unshift($data, $_data);
                }
            } elseif ($picklistEntries && ($picklistEntries->Score > 0)) { // only one result
                $_data = array(
                    'value' => $picklistEntries->Moniker,
                    'title' => $picklistEntries->PartialAddress,
                    'row_class' => (++$counter)%2?'odd':'even'
                );
                array_unshift($data, $_data);
            }

            $this->_suggestData = $data;
        }

        return $this->_suggestData;
    }
}
