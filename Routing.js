var SENSITIVITY = 0.001;

var __stupid_global_variable$Anjali33Adeeb = false;
function calculateAndDisplayRoute(start, end, obstructions, directionService, rend, OK, waypts) { 
  if(__stupid_global_variable$Anjali33Adeeb === true)
	  return;
  var wp = [];
  waypts.slice(0, Math.min(8, waypts.length)).forEach(function(e){
	  wp.push({location: {lng: parseFloat(e[0]), lat: parseFloat(e[1])}, stopover: true});});
  
  console.log("wp " + wp);

  directionService.route({
    origin: start,
    destination: end,
	waypoints: wp,
	optimizeWaypoints: true,
    travelMode: google.maps.TravelMode.WALKING
  },
	function(response, status) {
		
		if (status === OK) {
			var fix = isClear(obstructions, response);
			console.log(fix);
			//alert(Object.keys(fix));
			if(Object.keys(fix).length === 0){
				__stupid_global_variable$Anjali33Adeeb = true;	
				rend.setDirections(response);
			}else if(waypts.length === 0){
				var perms = choices(fix);
				console.log(perms);
				for(var i = 0; i < perms.length; i++){
					if (__stupid_global_variable$Anjali33Adeeb === true)
						return;
					else
						calculateAndDisplayRoute(start, end, obstructions, 
							directionService, rend, OK, perms[i]);
				}
			}else //nothing to be done...would send mg to usr	
				rend.setDirections(response);
		} else {
			//window.//alert('Directions request failed due to ' + status);
		}
	} 
	  // for each combination of fix which is list of lists of waypoints, recur 
	  // and recur only this once
	  //if that combo gets a valid path, then with global var, don't continue recur calls
	  // if that fails, then screw the handicapped, none of the approximate solutions failed
	);
}

function choices(fix){
	var scats = [];
	for(var key in fix){
		scats.push(fix[key][0]);
	}


	
	return [scats];
}

function testIntersect(wp1, wp2, obs){
	var m = (wp2.lat()-wp1.lat())/(wp2.lng()- wp1.lng());
	var ob = [parseFloat(obs.lng),parseFloat(obs.lat)];
	return (Math.abs(-m*ob[0] + ob[1] + m*wp1.lng() - wp1.lat() )) < SENSITIVITY * Math.sqrt(m*m + 1); // eyeballing the significatn difference
	
}

function isClear(obstructions, directions){
	
	var wps = directions.routes[0].overview_path;
	var scat = {};
	for(var i=0;i<(wps.length-1);i++){
		obstructions.forEach(function(obs){
			if(obs.active === '1' && testIntersect(wps[i], wps[i + 1], obs)){
				//alert('ti wrk');
				scat[obs.id + ''] = (scatters([parseFloat(obs.lng), parseFloat(obs.lat)])); // scatters given a obs point, will return an array of waypoints per that obs
			}	
		});
	}
	return scat;
}

function scatters(wp){
	var r = SENSITIVITY + 0.0001;//          WATCH !!
	return [[wp[0],wp[1]+r],
		[wp[0]-r,wp[1]],
		[wp[0] + r,wp[1]],
		[wp[0],wp[1]-r]];
	
}

var fkLatLng = function(x, y){
	this.lat = function(){
		return y;
	};
	this.lng = function(){
		return x;
	};
};

console.log(testIntersect(new fkLatLng(1,1), new fkLatLng(6,1), {lng: 1.05 , lat: 1.09}));
