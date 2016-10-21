# Yii2 Map Picker based on Yandex.Maps

A map picker for latitude, longtitude and zoom attributes of the model. This widget uses (Yandex.Maps)[http://maps.yandex.ru] API. Fill three important location attributes with one click!
Requires jQuery.

# Installation

The preferred way to install this extension is through composer.
Either run
```composer require nkizza/yii2-map-pick```
or add
```nkizza/yii2-map_pick```
to the require section of your application's composer.json file.

# Usage

The widget provides three available values: latitude, longtitude and zoom. The complete definition of the field is:
```
<?= MapPick::widget([
	...
	'attributes' => [ 		//key - must be a "zoom", "lat" or "lon"
		'zoom' => [
			'name'=>'MapsCoords[zoom]', //input name (may be custom)
			'value'=>7, 				//initial value for input 
		]
	],
]);?>
```

You may define only needed attributes. Please refer to the following code to see the examples:

```
<?= MapPick::widget([
	'model'=>$model,
	
	//html options for the container tag
	'options'=>[
		'style'=>'height:400px',
	], 
	
	//model attributes 
	'attributes' => [
		'lat', 					//latitude (your model must have the 'lat' field)
		'lon'=>'longtitude', 	// your model has the 'longtitude' field, place it under the key 'lon')
		'zoom' => [
			'name'=>'MapsCoords[customzoom]', 	//custom input name for attribute (if needed)
			'value'=>7, 						//initial value for input 
		]
	],
	
	//custom js callback, is optional
	'callback' => 'function(lat, lon, zoom) {alert('Hello!');}',
	
	//language identifier 
	'language' => 'en_US', 
]);?>
```

