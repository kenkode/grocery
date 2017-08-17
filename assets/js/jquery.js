$(function () {
  var current_row;

  $("#update-gas").click(function () {
    var type = $("input[name=gas_type]").val();
    if (type.length > 0) {
      $.ajax({
        url: "/gas_express/add_gas_type",
        type: "post",
        data: {
          'type': type
        },
        success: function (data) {
          $.notify({
            icon: 'pe-7s-check',
            message: "Successfully added " + type
          }, {
            type: 'success',
            timer: 1000
          });
          $("select#gas_name").append("<option value=" + data + ">" + type + "</option>");
          $("input[name=gas_type]").val("");
        }
      });
    } else {
      $.notify({
        icon: 'pe-7s-attention',
        message: "Invalid input detected."

      }, {
        type: 'warning',
        timer: 1000
      });
    }
  });

  $("#add-gas").click(function () {
    var type = $("select#gas_name option:selected").val();
    var name = $("select#gas_name option:selected").text();
    var size = $("input[name=gas_size]").val();
    var price = $("input[name=gas_price]").val();

    if (size.length < 1 || price.length < 1) {
      $.notify({
        icon: 'pe-7s-attention',
        message: "Invalid input detected."

      }, {
        type: 'warning',
        timer: 1000
      });
    } else {
      $.ajax({
        url: "/gas_express/add_gas",
        type: "post",
        data: {
          'type': type,
          'size': size,
          'price': price
        },
        success: function (data) {
          if (data == "E") {
            $.notify({
              icon: 'pe-7s-attention',
              message: "Gas already added."

            }, {
              type: 'warning',
              timer: 1000
            });
          } else {
            $.notify({
              icon: 'pe-7s-check',
              message: "Successfully added."

            }, {
              type: 'success',
              timer: 1000
            });
            $("#gas_table tbody").append('<tr><td>' + name + '</td><td>' + size + '</td><td><button type="button" class="remove_acc btn btn-default"><i class="pe-7s-trash"></i></button></td></tr>');
          }

          $("input[name=gas_size]").val("");
          $("input[name=gas_price]").val("");
        }
      });
    }
  });

  $("button#add_accessory").click(function () {
    var name = $("input#acc_name").val();
    var price = $("input#acc_price").val();

    if (name.length < 1 || price.length < 1) {
      $.notify({
        icon: 'pe-7s-attention',
        message: "Fill in required details"

      }, {
        type: 'warning',
        timer: 1000
      });
    } else {
      $.ajax({
        url: "/gas_express/add_accessory",
        type: "post",
        data: {
          'name': name,
          'price': price
        },
        success: function (data) {
          $.notify({
            icon: 'pe-7s-check',
            message: "Successfully added " + name
          }, {
            type: 'success',
            timer: 1000
          });
          $("table#accessories_table tbody").append('<tr><td>' + data + '</td><td>' + name + '</td><td>' + price + '</td><td><button type="button" class="remove_acc btn btn-default"><i class="pe-7s-trash"></i></button></td></tr>');
          $("input#acc_name").val("");
          $("input#acc_price").val("");
        }
      });
    }
  });

  $("button#add_gas").click(function () {
    var price = $("input#price").val();

    if (price.length < 1) {
      $.notify({
        icon: 'pe-7s-attention',
        message: "Fill in required details"

      }, {
        type: 'warning',
        timer: 1000
      });
    } else {
      $.ajax({
        url: "/gas_express/add_bulkgas",
        type: "post",
        data: {
          price: price
        },
        success: function (data) {
          $.notify({
            icon: 'pe-7s-check',
            message: "Successfully updated"
          }, {
            type: 'success',
            timer: 1000
          });
          $("table#gas_table tbody tr:first-child td:nth-child(2)").html(price);
          $("input#price").val("");
        }
      });
    }


  });

  $("button#add_service").click(function () {
    var name = $("input#service_name").val();

    if (name.length < 1) {
      $.notify({
        icon: 'pe-7s-attention',
        message: "Fill in required details"

      }, {
        type: 'warning',
        timer: 1000
      });
    } else {
      $.ajax({
        url: "/gas_express/add_service",
        type: "post",
        data: {
          'name': name
        },
        success: function (data) {
          $.notify({
            icon: 'pe-7s-check',
            message: "Successfully added " + name
          }, {
            type: 'success',
            timer: 1000
          });
          $("table#service_table tbody").append('<tr><td>' + name + '</td><td><button type="button" class="remove_service btn btn-default"><i class="pe-7s-trash"></i></button></td></tr>');
          $("input#service_name").val("");
        }
      });
    }
  });

  //Delete
  $(".remove_gas_button").on("click", function () {
    var id = $(this).data('id');
    var size = $(this).data('size');

    $.ajax({
      url: "/gas_express/remove_size",
      type: "post",
      data: {
        id: id,
        size: size
      },
      success: function (data) {
        $.notify({
          icon: 'pe-7s-check',
          message: "Gas removed"
        }, {
          type: 'success',
          timer: 1000
        });
        $(this).parentsUntil("tbody").hide();
      }
    });
  });

  $(".remove_product").on("click", function () {
    var id = $(this).data("id");
    var type = $(this).data("type");

    $.ajax({
      url: "/gas_express/remove_product",
      type: "post",
      data: {
        id: id,
        type: type
      },
      success: function (data) {
        $.notify({
          icon: 'pe-7s-check',
          message: "Successfully removed"
        }, {
          type: 'success',
          timer: 1000
        });
        $(this).parentsUntil("tbody").hide();
      }
    });
  });

  $("button#update_status").click(function () {
    var root = $(this);
    var status = root.data('status');
    var order = root.data('order');

    $.ajax({
      url: "/gas_express/status",
      type: "post",
      data: {
        id: order,
        status: status
      },
      success: function (data) {
        if (data == 0) {
          root.text("Mark Complete");
          $('h4#status span').text("Pending");
        } else {
          root.text("Mark Incomplete");
          $('h4#status span').text("Complete");
        }
        root.data('status', 1);

        $.notify({
          icon: 'pe-7s-check',
          message: "Updated Successfully"
        }, {
          type: 'success',
          timer: 1000
        });
      }
    });
  });

// Edit items
// edit-modal
$(".edit").click(function() {
  current_row = $(this);
  var id = $(this).data("id");
  var type = $(this).data("type");
  var name = $(this).parentsUntil("tbody").children("td:nth-child(1)").text();
  var disAttr = $("#edit-modal input[name=edit_price]").attr('disabled');
  if(typeof disAttr !== typeof undefined && disAttr !== false) {
    $("#edit-modal input[name=edit_price]").removeAttr('disabled');
  }
  if(type == 1 || type == 0) {
    var row;
    if(type == 1) {
      row = 2;
    }else {
      row = 3;
    }
    var price = $(this).parentsUntil("tbody").children("td:nth-child(" + row + ")").text();
    $("#edit-modal input[name=edit_price]").val(price);
    $("#edit-modal h4.modal-title").text(name);
  }else {
    $("#edit-modal input[name=edit_price]").val("N/A");
    $("#edit-modal input[name=edit_price]").prop('disabled', 'disabled');
    $("#edit-modal h4.modal-title").text(name);
  }
  $("#edit-modal input[name=edit_name]").val(name);
  $("#edit-modal").modal();
});

$("#edit-modal button#save_changes").click(function() {
  var type = current_row.data("type");
  var name = $("#edit-modal input[name=edit_name]").val();
  var price = "";
  var gid = "";
  var id = current_row.data('id');
  if(type == 1 || type == 0) {
    if(type == 0) {
      gid = current_row.data("gid");
    }
    price = $("#edit-modal input[name=edit_price]").val();
  }

  $.ajax({
    url: '/gas_express/update_item',
    type: "post",
    data: {
      id: id,
      gid: gid,
      name: name,
      price: price,
      type: type
    },
    success: function(data) {
      $("#edit-modal").modal('hide');
      var row;
      if(type == 1) {
        row = 2;
      }else {
        row = 3;
      }
      current_row.data("id", id);
      // var firstRows = current_row.parentsUntil("tbody").children("td");
      current_row.parentsUntil("tbody").children("td:nth-child(1)").text(name);
      if(type == 1 || type == 0) {
        current_row.parentsUntil("tbody").children("td:nth-child(" + row + ")").text(price);
      }
      $.notify({
        icon: 'pe-7s-check',
        message: "Updated Successfully"
      }, {
        type: 'success',
        timer: 1000
      });
    }
  });
});
});
