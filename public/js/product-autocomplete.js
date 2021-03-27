$(function() {
    var searchRequest = null;
    $("#search").keyup(function() {
        var minlength = 3;
        var that = this;
        var value = $(this).val();
        var entitySelector = $("#entitiesNav").html('');
        if (value.length >= minlength ) {
            if (searchRequest != null)
                searchRequest.abort();
            searchRequest = $.ajax({
                type: "GET",
                url: $(this).data('url'),
                data: {
                    'q' : value
                },
                dataType: "text",
                success: function(msg){
                    //we need to check if the value is the same
                    if (value==$(that).val()) {
                        var result = JSON.parse(msg);
                        $.each(result, function(key, arr) {
                            $.each(arr, function(id, value) {
                                if (key == 'entities') {
                                    if (id != 'error') {
                                        entitySelector.append('<li class="list-group-item d-flex">' +
                                            '<div class="flex-fill mr-2">' +
                                                '<img src="https://via.placeholder.com/200x150" width="64" alt="Product image">'+
                                            '</div>' +
                                            '<div class="flex-fill mr-2">'+
                                                '<h5 class="mt-0 mb-0">'+value.name+'</h5>'+
                                                '<div class="form-inline mt-2">'+
                                                    '<div class="form-group mb-0 mr-2">'+
                                                        "<input type='text'class='form-control' name='quantity["+id+"]' value='"+value.cart_quantity+"'/>"+
                                                        '<button type="button" class="add_cart_item btn btn-warning" data-product-id="'+id+'">Add</button>'+
                                                    '</div>'+
                                                '</div>'+
                                            '</div>' +
                                        '</li>');
                                    } else {
                                        entitySelector.append('<li class="errorLi">'+value+'</li>');
                                    }
                                }
                            });
                        });
                    }
                }
            });
        }
    });

    $("body").on('click', '.add_cart_item', function(){

        var productId = $(this).data('product-id');
        var quantity = $('input[name="quantity[' + productId + ']"]').val();
        if(quantity < 1){
            alert('Please set quantity larger than 0');
            return;
        }

        $.ajax({
            type: "POST",
            url: "/cart/add",
            data: {
                quantity: quantity,
                product_id: productId
            },
            dataType: "text",
            success: function(msg){
                var result = JSON.parse(msg);
                if(!result.status){
                    alert(result.message);
                    return;
                }
                location.reload();
            }
        });
    });
});
