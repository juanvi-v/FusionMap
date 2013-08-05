<?php
/**
 * FusionMap abstract map tools
 *
 * PHP version 5
 *
 * Copyright (c) 2013, Juanvi Vercher
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2013, Juanvi Vercher
 * @link          www.artvisual.net
 * @package       FusionMap
 * @subpackage    FusionMap.helpers
 * @since         v 0.2.0 (18-Jul-2013)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */
class FusionMapHelper extends Helper {



	public function map($map_id='map_layer',$options=array()){


		$out='
		<script type="text/javascript">
		//<![CDATA[

		var map;

		function init() {

			map = new OpenLayers.Map("'.$map_id.'",{
	    		allOverlays: true
	    	});

	    	var osm = new OpenLayers.Layer.OSM();
	    	//var gmap = new OpenLayers.Layer.Google("Google Streets", {visibility: false});

	    	//note that first layer must be visible
	    	//map.addLayers([osm, gmap]);
			map.addLayers([osm]);';

			if(!empty($options['points'])):
			$first=array();
			foreach($options['points'] as $point_index=>$point):

	    	if(!empty($point['longitude']) && !empty($point['latitude'])){
	    		if(empty($first)){
					$first=$point;

					if((intval($first['longitude'])==$first['longitude']) && (intval($first['latitude'])==$first['latitude'])){
						$zoom=5;
					}
					else{
						$zoom=12;
					}
					$out.='
					var map_center = new OpenLayers.LonLat(' . $first['longitude']*1.0 . ',' . $first['latitude']*1.0 . ').transform(
					new OpenLayers.Projection("EPSG:4326"),
					map.getProjectionObject()
					);
					map.setCenter(map_center, '.$zoom.');

					var markers = new OpenLayers.Layer.Markers( "Markers" );
					map.addLayer(markers);
					';
	    		}
			$out.='
			var point_'.$point_index.' = new OpenLayers.LonLat(' . $point['longitude']*1.0 . ',' . $point['latitude']*1.0 . ').transform(
			new OpenLayers.Projection("EPSG:4326"),
			map.getProjectionObject()
			);
			var size = new OpenLayers.Size(25,35);
			var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
			var icon = new OpenLayers.Icon("'.$this->url(FUSIONMAP_DEFAULT_MARKER).'", size, offset);
			markers.addMarker(new OpenLayers.Marker(point_'.$point_index.',icon));
			';

		}
		endforeach;
		endif; //empty points
		$out.='
	    	//map.addControl(new OpenLayers.Control.LayerSwitcher());
	    	//map.zoomToMaxExtent();
		}
		$(window).load(init());
		//]]>
		</script>
		';


		return $out;
	}



	/**
	 * put two points and draw route using google maps
	 * uses route, distance and duration calculation too specific to google maps.
	 * @param string $map_id
	 * @param array $options
	 * 		'makers'=>array('image1','image2'), images for custom markers
	 * 		'line_color'=> color for route
	 * 		'distance_id' => DOM id for distance field
	 * 		'duration_id' => DOM id for duration field
	 * 		'callback'	=>	 callback function name to be called after updating map
	 */
	public function double_point_map($map_id='map_layer',$options=array()){
		$renderer_options=array();
		if(!empty($options['line_color'])){
			$renderer_options['polylineOptions']=array('strokeColor'=>$options['line_color']);
		}
		if(!empty($options['markers'])){
			$renderer_options['suppressMarkers']=true;
		}
		$renderer_options_json=json_encode($renderer_options);

		$out='
		<script type="text/javascript">
			//<![CDATA[

				var directionsDisplay;
				var directionsService = new google.maps.DirectionsService();
				var map;


				function initialize() {
				  directionsDisplay = new google.maps.DirectionsRenderer('.$renderer_options_json.');
				  var from_point = new google.maps.LatLng('.$options['from_point']['latitude'].', '.$options['from_point']['longitude'].');
				  var to_point = new google.maps.LatLng('.$options['to_point']['latitude'].', '.$options['to_point']['longitude'].');

				  var mapOptions = {
				    zoom:7,
				    mapTypeId: google.maps.MapTypeId.ROADMAP,
				    center: from_point
				  }


				  map = new google.maps.Map(document.getElementById(\''.$map_id.'\'), mapOptions);
				  directionsDisplay.setMap(map);

				  ';


				  if(!empty($options['markers'])){
				  	$out.='
				  		icon_from={
				  			url:\''.$this->url('/img/markers/marker_1.png').'\',
				  			size: new google.maps.Size(26,32),
				  			origin: new google.maps.Point(0,0),
				  			anchor: new google.maps.Point(13,32)
				  		};
				  		icon_to={
				  			url:\''.$this->url('/img/markers/marker_2.png').'\',
				  			size: new google.maps.Size(26,32),
				  			origin: new google.maps.Point(0,0),
				  			anchor: new google.maps.Point(13,32)
				  		};
				  		marker_from=new google.maps.Marker({
				  			position: from_point,
				  			icon: icon_from,
				  			map: map
				  		});
				  		marker_to=new google.maps.Marker({
				  			position: to_point,
				  			icon: icon_to,
				  			map: map
				  		});
				  	';
				  }
				  $out.='

				  var request = {
				      origin:from_point,
				      destination:to_point,
				      travelMode: google.maps.DirectionsTravelMode.DRIVING,
				      unitSystem: google.maps.UnitSystem.METRIC
				  };
				  directionsService.route(request, function(response, status) {
				    if (status == google.maps.DirectionsStatus.OK) {
				  ';
				if(!empty($options['distance_id'])){
					$out.='
						  if(response.routes[0].legs[0].distance.value !== undefined){
						  	document.getElementById(\''.$options['distance_id'].'\').value=response.routes[0].legs[0].distance.value;
						  }
					';
				}

				if(!empty($options['duration_id'])){
					$out.='
						if(response.routes[0].legs[0].duration.value !== undefined){
							document.getElementById(\''.$options['duration_id'].'\').value=response.routes[0].legs[0].duration.value;
						}
					';
				}
				if(!empty($options['callback'])){
					$out.='
						'.$options['callback'].'()
					';

				}

				$out.='
					    directionsDisplay.setDirections(response);
				    }
				  });


				}

				google.maps.event.addDomListener(window, \'load\', initialize);

			//]]>
			</script>
		';

		return $out;
	}

	public function center($options=array()){
			if(!empty($options['long']) && !empty($options['lat']) && !empty($options['count'])){
				if((intval($options['long'])==$options['long']) && (intval($options['lat'])==$options['lat'])){
					$zoom=5;
				}
				else{
					$zoom=12;
				}
			$out='
			<script type="text/javascript">
			//<![CDATA[
				function set_center_'.$options['count'].'(){
				var map_center = new OpenLayers.LonLat(' . $options['long']*1.0 . ',' . $options['lat']*1.0 . ').transform(
				new OpenLayers.Projection("EPSG:4326"),
				map.getProjectionObject()
				);
				map.setCenter(map_center, '.$zoom.');
				}
			//]]>
			</script>
			';

			return $out;
		}
		else{
			return false;
		}
	}
	/*public function addMarker($options=array()){
		$out='
		var map_center = new OpenLayers.LonLat(' . $options['long']*1.0 . ',' . $options['lat']*1.0 . ').transform(
				new OpenLayers.Projection("EPSG:4326"),
				map.getProjectionObject()
		);

		var markers = new OpenLayers.Layer.Markers( "Markers" );
		map.addLayer(markers);

		var size = new OpenLayers.Size(25,35);
		var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
		var icon = new OpenLayers.Icon("'.$this->url(FUSIONMAP_DEFAULT_MARKER).'", size, offset);
		markers.addMarker(new OpenLayers.Marker(map_center,icon));';
		return($out);
	}*/


}