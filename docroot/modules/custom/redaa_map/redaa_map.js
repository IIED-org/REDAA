/**
 * JavaScript function to create a sidebar.
 *
 * This function should be added to a custom JavaScript file that is included
 * on the page with the Leaflet map.
 */

// (function ($, Drupal, drupalSettings) {
//     Drupal.behaviors.redaa_map_create_sidebar = {
//       attach: function (context, settings) {
//         // Make sure the map is fully initialized before trying to use it.
//         $(document).ready(function (layer) {
//               // This is the function that will be called when the popup is opened.
//               // You can put your custom JavaScript code here.
//               var sidebar = L.control.sidebar('sidebar', {
//                 position: 'right',
//                 container: 'sidebar'
//               });
            
//               // Add the sidebar to the map.
//               map.addControl(sidebar);
            
//               // Set the content of the sidebar to the feature's properties.
//               sidebar.setContent(layer.feature.properties.popupContent);
            
//               // Open the sidebar when the feature is clicked.
//               layer.on('click', function () {
//                 sidebar.show();
//               });

//         });
//       }
//     };
//   })(jQuery, Drupal, drupalSettings);

function redaa_map_create_sidebar(layer) {
  // Create a sidebar element.
  var sidebar = L.control.sidebar('sidebar', {
    position: 'right',
    container: 'sidebar'
  });

  // Add the sidebar to the map.
  map.addControl(sidebar);

  // Set the content of the sidebar to the feature's properties.
  sidebar.setContent(layer.popup.value);

  // Open the sidebar when the feature is clicked.
  layer.on('click', function () {
    sidebar.show();
  });
}