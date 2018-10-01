// function dump(obj) {
//     var out = "";
//     if(obj && typeof(obj) == "object"){
//         for (var i in obj) {
//             out += i + ": " + obj[i] + "\n";
//         }
//     } else {
//         out = obj;
//     }
//     alert(out);
// }
// 
// 
// $(document).ready(function() {
// 	$.contextMenu({
// 		selector: '.tmp-tab', 
// // 		callback: function(key, options) {
// // 			var m = "clicked: " + key;
// // 			window.console && console.log(m) || alert(m); 
// // 		},
// 		zIndex:90,
// 		events: {
// 			show: function(opt) {
// 				$(this).addClass('tmp-tab-act');
// 			},
// 			hide: function(opt) {
// 				$(this).removeClass('tmp-tab-act');
// 			}
// 		},
// 		items: {
// 			"edit": {name: "Clickable", icon: "edit", href: 'dadada', target: "netnetnet", callback:function(key,opt){alert('go to datata')}},
// 			"cut": {
// 				name: "Disabled", 
// 				icon: "cut", 
// 				disabled: function(key, opt) { 
// 					// this references the trigger element
// 					return !this.data('cutDisabled'); 
// 				}
// 			},
// 			"toggle": {
// 				name: "Toggle", 
// 				callback: function() {
// 					// this references the trigger element
// 					this.data('cutDisabled', !this.data('cutDisabled'));
// 					return false;
// 				}
// 			},
// 			
// 			"fold1": {
//                 "name": "actions", 
//                 "items": {
//                     "fold1-key1": {"name": "delete"},
//                     "set state": {
//                         "name": "set state", 
// 						callback: function(key,opt) {
// 							location.href('/set_state/0');
// 						},
//                         "items": {
//                             "fold2-key1": {
// 								"name": "not available",
// 								"value": "state0", 
// 						callback: function(key,opt) {
// 							location.href = '/set_state/0';
// 						},
// 							},
//                             "fold2-key2": {
// 								"name": "ready",
// 								"value": "state1", 
// 						callback: function(key,opt) {
// 							alert(1);
// 						},
// 							},
//                             "fold2-key3": {
// 								"name": "done",
// 								"value": "state2", 
// 						callback: function(key,opt) {
// 							alert(2);
// 						},
// 							},
//                         }
//                     },
//                     "fold1-key3": {"name": "delta"}
//                 }
//             }
// 			
// 		}
// 	});
// 	
// $.contextMenu({
// 		selector: '.tmp-tab td', 
// 		callback: function(key, options) {
// 			var m = "clicked: " + key;
// 			window.console && console.log(m) || alert(m); 
// 		},
// 		zIndex:90,
// 		events: {
// 			show: function(opt) {
// 				$(this).addClass('tmp-tab-act');
// 			},
// 			hide: function(opt) {
// 				$(this).removeClass('tmp-tab-act');
// 			}
// 		},
// 		items: {
// 			"sep1": '-------',
// 			"edit": {name: "Clickable", icon: "edit"},
// 			"cut": {
// 				name: "Disabled", 
// 				icon: "cut", 
// 				disabled: function(key, opt) { 
// 					// this references the trigger element
// 					return !this.data('cutDisabled'); 
// 				}
// 			},
// 			"toggle": {
// 				name: "Toggle", 
// 				callback: function() {
// 					// this references the trigger element
// 					this.data('cutDisabled', !this.data('cutDisabled'));
// 					return false;
// 				}
// 			}
// 		}
// 	});
// 
// });