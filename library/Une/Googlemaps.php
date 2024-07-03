<?php

/**
*
* Clase para la integracion de mapas de google
*
* @category Une
* @package Une_Maps
* @version 0.1
* @author Alton Bell-Smythe abellsmythe@gmail.com
* @mail une.ddti.info@gmail.com
* 
* API Password: AIzaSyD3o_tYrVpgHEcZtvSWnVJI87OtUjy_Uw0 
*
*/

class Une_Googlemaps {

	/**
     * Funcion para configurar las propiedades del Mapa
     *	
     * @return none
     */
	public function setProperties(){
		
	}

	/**
     * Funcion generar Mapa
     *	
     * @return string
     */
	public function setMarkup($name){
		// Modal (requires Bootstrap) & Styles
		$HTML  = '<div class="modal fade" id="modalMap" tabindex="-1" role="dialog" aria-labelledby="mapLabel"><div class="modal-dialog modal-lg" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="mapLabel">Ubicacion - Mapa</h4></div><div class="modal-body"><div id="googleMap" class="map"></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" style="background: #00787A;">Guardar</button></div></div></div></div><style>.map { display: block; width: 100%; height: 50em; margin: 10px auto; border: 0.5px solid #666; border-radius: 5px; box-shadow: 0px 0px 5px #CCC; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; }</style><div id="mapScript"></div>';
		return $HTML;
	}

	/**
     * Funcion generar Mapa
     *	
     * @return string
     */
	public function setMap($name){
		$HTML .= '<script>var GoogleLat = null, GoogleLng = null; function getLocation(){ if (navigator.geolocation){ navigator.geolocation.getCurrentPosition(function(position){ GoogleLat   = position.coords.latitude; GoogleLng   = position.coords.longitude; var place   = new google.maps.LatLng(GoogleLat,GoogleLng), markers = []; var mapProp = { center:place, zoom:18, mapTypeId:google.maps.MapTypeId.ROADMAP }; var map = new google.maps.Map(document.getElementById("googleMap"),mapProp); var marker = new google.maps.Marker({ position:place, title: "'.$name.'" }); marker.setMap(map); markers.push(marker); var infowindow = new google.maps.InfoWindow({ content: "'.$name.'" }); google.maps.event.addListener(marker, "click", function(){ infowindow.open(map,marker); }); infowindow.open(map,marker); google.maps.event.addListener(map, "click", function(event){ placeMarker(event.latLng); }); function placeMarker(location){ deleteMarkers(); var mark = new google.maps.Marker({ position: location, map: map }); markers.push(mark); var infowindow = new google.maps.InfoWindow({ content: "'.$name.'" }); infowindow.open(map,mark); GoogleLat = location.lat(); GoogleLng = location.lng(); } function deleteMarkers(){ for(i in markers){ markers[i].setMap(); } } }); } } $("#modalCancel").click(function(){ GoogleLat = null; GoogleLng = null; });</script>';
		return $HTML;
	}

}

?>