//Create a map from numeric Model ID to helpful string
var model_map = {};
$.get('GetModels.php').then(function(response){
  $.each(response, function(k, v){
    model_map[v.ID] = v.Name;
  });
});


function fill_table(data, $table){
  if( ! ($table instanceof jQuery)){
    $table = $($table);
  }

  $.each(data.Terms, function(terms_idx, term){
    //It's impractical to find rows by the up-to-date value of the input in the th http://stackoverflow.com/a/15031698/8995
    var $row = $table.find("tbody tr:eq("+terms_idx+")");
    $.each(term.Programs, function(programs_idx, program){
      if(program.Code){
        var $cell = $("<td>" +
          "$" + program.Payment + " / mo"+
          "&nbsp;&nbsp;<i class='glyphicon glyphicon-info-sign'></i>" +
        "</td>");
        $cell.find("i").popover({
          content:"<pre>" + JSON.stringify(program, null, 2) + "</pre>",
          html:true,
          title:"Deal Details"
        });
        $row.append($cell);
      }else{
        $row.append("<td> - </td>");
      }
    });
  });
}

$("#recalculate").click(function(){
  $("#recalculate").button('loading');

  var Lease = {Cash:[], Term:[] }, Retail = {Cash:[], Term:[]};
  $("#lease .down").each(function(){ Lease.Cash.push( $(this).val() ); });
  $("#lease .term").each(function(){ Lease.Term.push( $(this).val() ); });
  $("#retail .down").each(function(){ Retail.Cash.push( $(this).val() ); });
  $("#retail .term").each(function(){ Retail.Term.push( $(this).val() ); });

  $("#lease tbody td, #retail tbody td").remove();

  $.post({
    url: 'mPencil.php',
    dataType: 'json',
    data: JSON.stringify({
      "Lease": Lease,
      "Retail" : Retail,
      "Vehicle" : Vehicle,
      "Price" : $("input[name=Price]").val()
    })
  }).done(function(response){

    fill_table(response.Lease, "#lease");
    fill_table(response.Retail, "#retail");

  }).always(function(){
    $("#recalculate").button('reset');
  });
});

$("#vehicle_lookup").on("submit", function(e){
  e.preventDefault();

  $("#about_vehicle, #payments").hide();

  $.get({
    url: 'GetVehiclesByVIN.php?' + $("#vehicle_lookup").serialize(),
    dataType: 'json',
  }).done(function(response){
    if(response === null){
      alert("Vehicle not found");
      return;
    }
    var found_vehicle = response[0];

    Vehicle = {
      "ID" : found_vehicle.ID,
      "TotalMSRP" : Math.floor(found_vehicle.MSRP * 1.1),
      "BaseMSRPAmount" : found_vehicle.MSRP,
      "CurrentMileage" : 0,
      "TotalDealerCost" : found_vehicle.Invoice || (found_vehicle.MSRP * 0.9)
    };

    $("#about_vehicle").text(
      ( found_vehicle.IsNew ? "New " : "Used ") +
      model_map[found_vehicle.ModelID] + " " +
      found_vehicle.ShortDescription + " " +
      "for $" + Vehicle.TotalMSRP
    ).show();

    $("input[name=Price]").val(Vehicle.TotalMSRP);

    $("#payments").show();
    $("#recalculate").click();

  }).fail(function(response){
    console.log(response);
    alert(
      (response.responseJSON && response.responseJSON.message) ?
        response.responseJSON.message :
        "Problem looking up vehicle"
    );
  });

});
