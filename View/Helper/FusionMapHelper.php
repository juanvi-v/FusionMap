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
	 * put two points and draw route
	 * @param unknown_type $map_id
	 * @param unknown_type $options
	 */
	public function double_point_map($map_id='map_layer',$options=array()){


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

		if(!empty($options['from_point']) && !empty($options['to_point'])):
				$from_point=$options['from_point'];
				$to_point=$options['to_point'];
				$first=$from_point;

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
				map.setCenter(map_center);


				var markers = new OpenLayers.Layer.Markers( "Markers" );
				map.addLayer(markers);
				';

			$out.='
			var from_point = new OpenLayers.LonLat(' . $from_point['longitude']*1.0 . ',' . $from_point['latitude']*1.0 . ').transform(
			new OpenLayers.Projection("EPSG:4326"),
			map.getProjectionObject()
			);

			var size = new OpenLayers.Size(25,35);

			var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);

			var icon = new OpenLayers.Icon("'.$this->url(FUSIONMAP_DEFAULT_MARKER).'", size, offset);

			markers.addMarker(new OpenLayers.Marker(from_point,icon));


			var to_point = new OpenLayers.LonLat(' . $to_point['longitude']*1.0 . ',' . $to_point['latitude']*1.0 . ').transform(
			new OpenLayers.Projection("EPSG:4326"),
			map.getProjectionObject()
			);

			var size = new OpenLayers.Size(25,35);

			var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);

			var icon = new OpenLayers.Icon("'.$this->url(FUSIONMAP_DEFAULT_MARKER).'", size, offset);

			markers.addMarker(new OpenLayers.Marker(to_point,icon));
			';



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