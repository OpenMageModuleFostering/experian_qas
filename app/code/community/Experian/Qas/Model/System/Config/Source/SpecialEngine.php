<?php
/**
 * Experian Qas Engine type source
 *
 * @category  Experian
 * @package   Experian_Qas
 * @copyright 2012 Experian
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Experian_Qas_Model_System_Config_Source_SpecialEngine 
    extends Experian_Qas_Model_System_Config_Source_Engine
{
    public function __construct()
    {
        $this->_configNodePath = 'global/experian_qas/specialengine/type';
    }
}
