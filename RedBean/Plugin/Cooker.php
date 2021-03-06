<?php
/**
 * RedBean Cooker
 *
 * @file			RedBean/Cooker.php
 * 
 * @plugin			public static function graph($array,$filterEmpty=false) { $c = new RedBean_Plugin_Cooker(); $c->setToolbox(self::$toolbox);return $c->graph($array,$filterEmpty);}
 * 
 * @desc			Turns arrays into bean collections for easy persistence.
 * @author			Gabor de Mooij and the RedBeanPHP Community
 * @license			BSD/GPLv2
 *
 * The Cooker is a little candy to make it easier to read-in an HTML form.
 * This class turns a form into a collection of beans plus an array
 * describing the desired associations.
 *
 * (c) copyright G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Plugin_Cooker implements RedBean_Plugin {
	/**
	 * Flag, determines whether it's possible to load beans with graph().
	 * @var boolean
	 */
	private static $loadBeans = false;
	/**
	 * This flag indicates whether empty strings in beans will be
	 * interpreted as NULL or not. TRUE means Yes, will be converted to NULL,
	 * FALSE means empty strings will be stored as such (conversion to 0 for integer fields).
	 * @var boolean
	 */
	private static $useNULLForEmptyString = false;
	/**
	 * If you enable bean loading graph will load beans if there is an ID in the array.
	 * This is very powerful but can also cause security issues if a user knows how to
	 * manipulate beans and there is no model based ID validation.
	 * 
	 * @param boolean $yesNo 
	 */
	public static function enableBeanLoading($yesNo) {
		self::$loadBeans = ($yesNo);
	}
	/**
	 * Sets the toolbox to be used by graph()
	 *
	 * @param RedBean_Toolbox $toolbox toolbox
	 * @return void
	 */
	public function setToolbox(RedBean_Toolbox $toolbox) {
		$this->toolbox = $toolbox;
		$this->redbean = $this->toolbox->getRedbean();
	}
	/**
	 * Turns an array (post/request array) into a collection of beans.
	 * Handy for turning forms into bean structures that can be stored with a
	 * single call.
	 * 
	 * Typical usage:
	 * 
	 * $struct = R::graph($_POST);
	 * R::store($struct);
	 * 
	 * Example of a valid array:
	 * 
	 *	$form = array(
	 *		'type'=>'order',
	 *		'ownProduct'=>array(
	 *			array('id'=>171,'type'=>'product'),
	 *		),
	 *		'ownCustomer'=>array(
	 *			array('type'=>'customer','name'=>'Bill')
	 *		),
	 * 		'sharedCoupon'=>array(
	 *			array('type'=>'coupon','name'=>'123'),
	 *			array('type'=>'coupon','id'=>3)
	 *		)
	 *	);
	 * 
	 * Each entry in the array will become a property of the bean.
	 * The array needs to have a type-field indicating the type of bean it is
	 * going to be. The array can have nested arrays. A nested array has to be
	 * named conform the bean-relation conventions, i.e. ownPage/sharedPage
	 * each entry in the nested array represents another bean.
	 *  
	 * @param	array   $array       array to be turned into a bean collection
	 * @param   boolean $filterEmpty whether you want to exclude empty beans
	 *
	 * @return	array $beans beans
	 */
	public function graph($array, $filterEmpty = false) {
      	$beans = array();
		if (is_array($array) && isset($array['type'])) {
			$type = $array['type'];
			unset($array['type']);
			//Do we need to load the bean?
			if (isset($array['id'])) {
				if (self::$loadBeans) {
					$id = (int) $array['id'];
					$bean = $this->redbean->load($type, $id);
				} else {
					throw new RedBean_Exception_Security('Attempt to load a bean in Cooker. Use enableBeanLoading to override but please read security notices first.');
				}
			} else {
				$bean = $this->redbean->dispense($type);
			}
			foreach($array as $property=>$value) {
				if (is_array($value)) {
					$bean->$property = $this->graph($value, $filterEmpty);
				} else {
					if($value == '' && self::$useNULLForEmptyString){
						$bean->$property = null;
                    } else $bean->$property = $value;
				}
			}
			return $bean;
		} elseif (is_array($array)) {
			foreach($array as $key=>$value) {
				$listBean = $this->graph($value, $filterEmpty);
				if (!($listBean instanceof RedBean_OODBBean)) {
					throw new RedBean_Exception_Security('Expected bean but got :'.gettype($listBean)); 
				}
				if ($listBean->isEmpty()) {  
					if (!$filterEmpty) { 
						$beans[$key] = $listBean;
					}
				} else { 
					$beans[$key] = $listBean;
				}
			}
			return $beans;
		} else {
			throw new RedBean_Exception_Security('Expected array but got :'.gettype($array)); 
		}
	}
	/**
	 * Toggles the use-NULL flag.
	 *  
	 * @param boolean $yesNo 
	 */
	public function setUseNullFlag($yesNo) {
		self::$useNULLForEmptyString = (boolean) $yesNo;
	}
}