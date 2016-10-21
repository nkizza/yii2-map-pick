<?php
/**
 * @copyright Copyright (c) 2016 Nastya Kizza
 * @link http://macrodigital.ru
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
 
 /**
  * Usage: 
  * <?= MapPick::widget([
  * 	'model'=>'model',
  * 	'options'=>[
  * 		'style'=>'height:400px',
  *		], //html options for the container tag
  * 	'attributes' => [
  * 		'lat', //latitude (your model must have the 'lat' field),
  * 		'lon'=>'longtitude' (your model has the 'longtitude' field, place it under the key 'lon'),
  * 		'zoom' => [
  * 			'name'=>'MapsCoords[customzoom]', //custom input name
  * 			'value'=>7, //initial value for input 
  * 		]
  * 	],
  * 	'callback' => 'function(lat, lon, zoom) {alert('Hello!');}', //custom js callback, is optional
  * 	'language' => 'en_US', //language identifier 
  * ]);?>
  */
  
namespace nkizza\mappick;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\base\Widget;
use yii\web\View;
use yii\base\InvalidConfigException;

/**
 *
 * Renders a Yandex map picker to pick coordinates of any object.
 *
 * @author Nastya Kizza <fire@fcfv.ru>
 * @link http://www.macrodigital.ru/
 */
class MapPick extends Widget
{
	public $model = null;
	public $options = [];
	public $attributes = [];
	public $callback = '';
	public $language = 'en_US';
	
    /**
     * @var array of the options for the Yandex Maps API.
     * @see https://tech.yandex.ru/maps/
     */
    public $clientOptions = [];
    
	/**
	 * Initializes the default values of widget params.
	 */
	private function initDefaults() {
		if(!isset($this->options['id'])) $this->options['id'] = 'ymaps_'.substr(md5(time()),0,6);
		
		if(empty($this->language)) $this->language = 'en_US';
		
		$modelClass = "";
		if(!empty($this->model)) {
			$reflection = new \ReflectionClass($this->model);
			$modelClass = $reflection->getShortName();
		}
		
		foreach($this->attributes as $key=>$value) {
			if(empty($modelClass) && is_numeric($key))
				throw new InvalidConfigException('You should define either name or model for field '.$value);
			if(empty($modelClass) && (!is_array($value) || !isset($value['name'])))
				throw new InvalidConfigException('You should define either name or model for field '.$value);
			
			if(is_numeric($key)) {
				$this->attributes[$value] = [
					'name' => $modelClass."[".$value."]",
					'value' => $this->model->$value,
					'id' => $this->getElementIdByName($modelClass."[".$value."]"),
				];
				unset($this->attributes[$key]);
			} elseif(!is_array($value)) {
				$this->attributes[$key] = [
					'name' => $modelClass."[".$value."]",
					'value' => $this->model->$value,
					'id' => $this->getElementIdByName($modelClass."[".$value."]"),
				];
			}
		}
		
		if(isset($this->attributes['zoom']) && empty($this->attributes['zoom']['value'])) $this->attributes['zoom']['value'] = 10; 
		
		if(empty($this->callback)) $this->callback = $this->getDefaultCallback();
		$this->callback = 'var my_'.$this->options['id'].'_click = '.$this->callback;
	} 
	
    /**
     * @inheritdoc
     */
    public function run() {
    	$this->initDefaults();
		
    	//html part
    	foreach($this->attributes as $key=>$value)
			echo Html::hiddenInput($value['name'], $value['value'], ['id' => $value['id']]);
		
    	echo Html::tag('div', '', $this->options);
    	
		//register scripts
        $this->registerClientScript();
    }
	
	private function getElementIdByName($name) {
		return implode("-", explode("_", str_replace(["[","]"], "_", $name))).$this->options['id'];
	}

    /**
     * Registers Yandex Map object and events
     */
    protected function registerClientScript() {
        $view = $this->getView();
		
		$url = '//api-maps.yandex.ru/2.1/?lang='.$this->language;
        $view->registerJsFile($url);
		
		//register init and callback
		$view->registerJs($this->getInit(), View::POS_END);
		$view->registerJs($this->callback, View::POS_END);
    }
	
	private function getDefaultCallback() {
		$js = "function(lat, lon, zoom) {";
		foreach($this->attributes as $key=>$attr)
			$js .= "\n\t".'$("input#'.$attr['id'].'").val('.$key.');';
		$js .= "\n}";
		$js = str_replace('[%ID%]', $this->options['id'], $js);
		return $js;		
	}
	
	private function getInit() {
		$js = '
			ymaps.ready(init_[%ID%]);
			var my_[%ID%];
			
			function init_[%ID%]() {
			    my_[%ID%] = new ymaps.Map("[%ID%]", {
			        center: [[%LAT%], [%LON%]], // Углич
			        zoom: [%ZOOM%]
			    });
				
				var mark_[%ID%] = new ymaps.Placemark([[%LAT%], [%LON%]]);
				my_[%ID%].geoObjects.add(mark_[%ID%]);				
								
			
			    my_[%ID%].events.add("click", function (e) {
			        var coords = e.get(\'coords\');
					mark_[%ID%].geometry.setCoordinates(coords);
					
					var zoom = my_[%ID%].getZoom();
					var lat = coords[0].toPrecision(10);
					var lon = coords[1].toPrecision(10);
					
					
					my_[%ID%]_click(lat, lon, zoom); 
			    });
			}				
		';
		$id = $this->options['id'];
		$lat = (!empty($this->attributes['lat']['value'])) ? $this->attributes['lat']['value'] : 0;
		$lon = (!empty($this->attributes['lon']['value'])) ? $this->attributes['lon']['value'] : 0;
		$zoom = (!empty($this->attributes['zoom']['value'])) ? $this->attributes['zoom']['value'] : 0; 
		
		return str_replace(
			["[%ID%]", "[%LAT%]", "[%LON%]", "[%ZOOM%]"], 
			[$id, $lat, $lon, $zoom],
			$js);
	}
}
