/**
 * @file
 * Google map.
 */

(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.fleur_google_maps = {
        attach: function (context, settings) {
            if (typeof google === "undefined"
                || typeof google.maps === "undefined"
                || typeof google.maps.Map === "undefined") {
                return;
            }

            var map_style = [
                    {
                        "elementType": "geometry",
                        "stylers": [
                            {
                                "color": "#f5f5f5"
                            }
                        ]
                    },
                    {
                        "elementType": "labels.icon",
                        "stylers": [
                            {
                                "visibility": "off"
                            }
                        ]
                    },
                    {
                        "elementType": "labels.text.fill",
                        "stylers": [
                            {
                                "color": "#616161"
                            }
                        ]
                    },
                    {
                        "elementType": "labels.text.stroke",
                        "stylers": [
                            {
                                "color": "#f5f5f5"
                            }
                        ]
                    },
                    {
                        "featureType": "administrative.land_parcel",
                        "elementType": "labels.text.fill",
                        "stylers": [
                            {
                                "color": "#bdbdbd"
                            }
                        ]
                    },
                    {
                        "featureType": "poi",
                        "elementType": "geometry",
                        "stylers": [
                            {
                                "color": "#eeeeee"
                            }
                        ]
                    },
                    {
                        "featureType": "poi",
                        "elementType": "labels.text.fill",
                        "stylers": [
                            {
                                "color": "#757575"
                            }
                        ]
                    },
                    {
                        "featureType": "poi.park",
                        "elementType": "geometry",
                        "stylers": [
                            {
                                "color": "#e5e5e5"
                            }
                        ]
                    },
                    {
                        "featureType": "poi.park",
                        "elementType": "labels.text.fill",
                        "stylers": [
                            {
                                "color": "#9e9e9e"
                            }
                        ]
                    },
                    {
                        "featureType": "road",
                        "elementType": "geometry",
                        "stylers": [
                            {
                                "color": "#ffffff"
                            }
                        ]
                    },
                    {
                        "featureType": "road.arterial",
                        "elementType": "labels.text.fill",
                        "stylers": [
                            {
                                "color": "#757575"
                            }
                        ]
                    },
                    {
                        "featureType": "road.highway",
                        "elementType": "geometry",
                        "stylers": [
                            {
                                "color": "#dadada"
                            }
                        ]
                    },
                    {
                        "featureType": "road.highway",
                        "elementType": "labels.text.fill",
                        "stylers": [
                            {
                                "color": "#616161"
                            }
                        ]
                    },
                    {
                        "featureType": "road.local",
                        "elementType": "labels.text.fill",
                        "stylers": [
                            {
                                "color": "#9e9e9e"
                            }
                        ]
                    },
                    {
                        "featureType": "transit.line",
                        "elementType": "geometry",
                        "stylers": [
                            {
                                "color": "#e5e5e5"
                            }
                        ]
                    },
                    {
                        "featureType": "transit.station",
                        "elementType": "geometry",
                        "stylers": [
                            {
                                "color": "#eeeeee"
                            }
                        ]
                    },
                    {
                        "featureType": "water",
                        "elementType": "geometry",
                        "stylers": [
                            {
                                "color": "#c9c9c9"
                            }
                        ]
                    },
                    {
                        "featureType": "water",
                        "elementType": "labels.text.fill",
                        "stylers": [
                            {
                                "color": "#9e9e9e"
                            }
                        ]
                    }
                ];

            /**
             * Defines the Popup class.
             * */
            function definePopupClass() {
                /**
                 * A customized popup on the map.
                 * @param {!google.maps.LatLng} position
                 * @param {!Element} content
                 * @constructor
                 * @extends {google.maps.OverlayView}
                 */
                Popup = function (position, content) {
                    this.position = position;

                    content.classList.add('popup-bubble-content');

                    var pixelOffset = document.createElement('div');
                    pixelOffset.classList.add('popup-bubble-anchor');
                    pixelOffset.appendChild(content);

                    this.anchor = document.createElement('div');
                    this.anchor.classList.add('popup-tip-anchor');
                    this.anchor.appendChild(pixelOffset);

                    // Optionally stop clicks, etc., from bubbling up to the map.
                    this.stopEventPropagation();
                };
                // NOTE: google.maps.OverlayView is only defined once the Maps API has
                // loaded. That is why Popup is defined inside initMap().
                Popup.prototype = Object.create(google.maps.OverlayView.prototype);

                /** Called when the popup is added to the map. */
                Popup.prototype.onAdd = function () {
                    this.getPanes().floatPane.appendChild(this.anchor);
                };

                /** Called when the popup is removed from the map. */
                Popup.prototype.onRemove = function () {
                    if (this.anchor.parentElement) {
                        this.anchor.parentElement.removeChild(this.anchor);
                    }
                };

                /** Called when the popup needs to draw itself. */
                Popup.prototype.draw = function () {
                    var divPosition = this.getProjection().fromLatLngToDivPixel(this.position);
                    // Hide the popup when it is far out of view.
                    var display =
                        Math.abs(divPosition.x) < 4000 && Math.abs(divPosition.y) < 4000 ?
                            'block' :
                            'none';

                    if (display === 'block') {
                        this.anchor.style.left = divPosition.x + 'px';
                        this.anchor.style.top = (divPosition.y - 32) + 'px';
                    }
                    if (this.anchor.style.display !== display) {
                        this.anchor.style.display = display;
                    }
                };

                /** Stops clicks/drags from bubbling up to the map. */
                Popup.prototype.stopEventPropagation = function () {
                    var anchor = this.anchor;
                    anchor.style.cursor = 'auto';

                    ['click', 'dblclick', 'contextmenu', 'wheel', 'mousedown', 'touchstart',
                        'pointerdown']
                        .forEach(function (event) {
                            anchor.addEventListener(event, function (e) {
                                e.stopPropagation();
                            });
                        });
                };
            }

            $('.fleur-contact-map .fleur-google-map', context).once('google-map').each(function () {
                definePopupClass();

                var uluru = {lat: -33.915878809349586, lng: 18.417606580796182};
                var uluru_center = {lat: -33.9108788, lng: 18.4135065};
                var map = new google.maps.Map(this, {
                    zoom: 15,
                    center: uluru_center,
                    styles: map_style,
                    mapTypeControl: false,
                    streetViewControl: false,
                });

                var contentString = $(this).parent().find('.map-info').clone()[0];

                var popup = new Popup(
                    new google.maps.LatLng(uluru.lat, uluru.lng),
                    contentString);
                popup.setMap(map);

                var image = {
                    url: drupalSettings.path.baseUrl + "themes/custom/palette_de_fleurs/js/google_maps/location-pointer.png",
                    size: new google.maps.Size(32, 32),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(16, 32)
                };

                var marker = new google.maps.Marker({
                    position: uluru,
                    map: map,
                    title: 'Palette de Fleurs',
                    icon: image,
                });

            });
        }
    }
})(jQuery, Drupal, drupalSettings);
