var token = '';
$( document ).ready(function() {
  $( "#submit" ).submit(function( event ) {
    event.preventDefault();
    var userName = $("#userName").val();
    var password= $("#password").val();
    console.log(userName);
    console.log(password);

    $.ajax({
      type:"POST",
      url: "restFinal.php/v1/user",
      contentType: 'application/json',
      data: JSON.stringify({user: userName, password: password}),
      success: function(data){
        if(data.status == "OK"){
          token = data.token;
          showButtons();
          showTables();
          $("#login").hide();
        }
        else{
          $("#login-info").show();
        }
      },
      error: function(data){
        alert(data.status);
      }
    });
  });
  $( "#show-author" ).click(function() {
    $("#login").hide();
    $("#author").show();
  });
  $( "#back-to-login" ).click(function() {
    $("#login").show();
    $("#author").hide();
  });
});


function showButtons() {
  $.ajax({
    type: "GET",
    contentType: 'application/json',
    url: "restFinal.php/v1/items",
    success: function (data) {
      if(data.status == "OK"){
        var html = "";
        $.each(data.items, function(i, item){
          html += "<button class=\"itemButton\" pk=" + item.pk + " onclick='recordItem(this)'>" + item.item + "</button>";
        });
        $("#buttons").append(html);
      } else{
        alert("v1 items api is not working");
      }
    }
  });
}


function recordItem(whichButton) {
  var itemData = {
    itemFK : $(whichButton).attr('pk'),
    token : token
  };
  $.ajax({
    type: "POST",
    data: JSON.stringify(itemData),
    contentType: 'application/json',
    url: "restFinal.php/v1/items",
    success: function (data) {
      if(data.status == "OK"){
        showTables();
      }
    }
  });
}

function showTables() {
  $("#tables").empty();
  $.ajax({
    type: "GET",
    contentType: 'application/json',
    url: "restFinal.php/v1/itemsSummary/" + token,
    success: function (data) {
      if(data.status == "OK"){
        var html = '';
        $.each(data.items, function(i, item){
          html += '<tr><td>' + item.item + '</td><td>' + item.count + '</td></tr>';
        });
        $("#tables").append(html);
        $.ajax({
          type: "GET",
          contentType: 'application/json',
          url: "restFinal.php/v1/items/" + token,
          success: function (data) {
            console.log(data);
            if(data.status == "OK"){
              var trHTML = '';
              $.each(data.items, function(i, item){
                trHTML += '<tr><td>' + item.item + '</td><td>' + item.timestamp + '</td></tr>';
              });
              $("#tables").append(trHTML);
            } else{
              alert("Failed");
            }
          },
          error: function(data){
            console.log(data);
          }
        });
      }
      else{
        alert("fail");
      }
    }
  });


}
