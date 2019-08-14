<?php
/**
 * Experian Qas Block - used for Singleline Engine
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Block_Interaction extends Mage_Core_Block_Template
{
    const INTERACTION_FORM_NAME = 'interaction-form';

    protected $_suggestData = null;
    protected $_template = 'qas/interaction.phtml';

    /**
     * Get interaction form name
     *
     * @return string
     */
    public function getInteractionFormName()
    {
        return self::INTERACTION_FORM_NAME;
    }

    /**
     * Format the addresses response for Intuitive Engine
     *
     * @return array
     */
    public function getSuggestData()
    {
        if (is_null($this->_suggestData)) {

            $picklistEntries = $this->getPicklistEntries();
            $response = array();
            $data = array();
            $counter = 0;

            if (is_array($picklistEntries)) {
                foreach ($picklistEntries as $entry) {
                    $_data = array(
                        'value' => $entry->Moniker,
                        'title' => $entry->PartialAddress,
                        'row_class' => (++$counter)%2?'odd':'even'
                    );
                    array_push($data, $_data);
                }
            } elseif ($picklistEntries && ($picklistEntries->Score > 0)) { // only one result
                $_data = array(
                    'value' => $picklistEntries->Moniker,
                    'title' => $picklistEntries->PartialAddress,
                    'row_class' => (++$counter)%2?'odd':'even'
                );
                array_push($data, $_data);
            }

            $this->_suggestData = $data;
        }
        return $this->_suggestData;
    }
}
