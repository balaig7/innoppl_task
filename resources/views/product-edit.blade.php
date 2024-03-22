@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card  p-3 shadow  mb-5 bg-body rounded">
        <div class="d-flex flex-row-reverse bd-highlight">
            <div class="p-2 bd-highlight">
                <a href="/" class="btn btn-danger float-end">Back</a>
            </div>
        </div>

        <div class="card-header p-3 bg-primary text-white my-3">Edit Product</div>

        <form method="post" id="update-product-form">
            @csrf
            <div class="mb-3">
                <label for="productName" class="form-label">Name<span class="text-danger">*</span></label>
                <input type="text" class="form-control mandatory-field" id="product_name" name="product_name" value="{{$product->name}}">
                <span class="invalid-feedback" role="alert"></span>
            </div>
            <div class="mb-3">
                <label for="productPrice" class="form-label">SKU</label>
                <input type="text" readonly value="{{$product->sku}}" class="form-control mandatory-field">
                <span class="invalid-feedback" role="alert"></span>
            </div>
            <div class="mb-3">
                <label for="productPrice" class="form-label">Price<span class="text-danger">*</span></label>
                <input type="text" name="price" id="price" class="form-control mandatory-field" placeholder="0.00" value="{{$product->amount}}">
                <span class="invalid-feedback" role="alert"></span>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" id="description" rows="3">{{$product->description}}</textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Upload Images<span class="text-danger">*</span></label>
                <input type="file" class="form-control mandatory-field" id="images" name="images[]" multiple>
                <span class="invalid-feedback" role="alert"></span>
            </div>

            @if(!empty($product->images!=''))
                <div class="row thumbnail-img my-3">
                    @foreach($product->images as $img)
                        <div class="col-md-4 p-2 position-relative">
                            <i class="fa fa-times remove-icon remove-product-thumnail-image text-center text-white" aria-hidden="true" data-product="{{$product->id}}" data-name="{{$img}}"></i>
                            <img class="rounded mx-auto thumnail-image border" id="thumbnail-image" src="/uploads/user_{{$product->created_by}}/{{$img}}">
                        </div>
                    @endforeach
                </div>
            @endif

            <button class="btn btn-primary" id="update-product" data-product="{{$product->id}}" type="button">Update</button>
        </form>
    </div>
</div>

@endsection
@push('css')
    <link href="{{asset('css/sweetalert2.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/fontawesome.min.css')}}" />

@push('scripts')
    <script src="{{asset('js/sweetalert2.all.min.js')}}"></script>
    <script src="{{asset('ckeditor/ckeditor.js')}}"></script>
@endpush
@push('custom-scripts')

<script>
    function isNumber(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }
    $(document).ready(function(){

        CKEDITOR.replace('description', {
            allowedContent: true,
            basicEntities: false,
            width: '100%',
        });
        CKEDITOR.dtd.$removeEmpty['i'] = false;
        CKEDITOR.instances['description'].on('change', function() {
            CKEDITOR.instances['description'].updateElement()
        });
    })

    $(document).on("click","#update-product",function(){
        
        var formData = new FormData($("#update-product-form")[0]);
        let product = $(this).data('product')
        $(".loader").show()

        $.ajax({
            type: "POST",
            url: '/api/product/'+product,
            data: formData,
            cache:false,
            contentType: false,
            processData: false,
            success: function(data) {
                $(".loader").hide()

                Swal.fire({
                    title: data.message,
                    text:'',
                    icon:data.status,
                }).then(function (result) {
                    location.reload()
                });
            },
            error: function(data) {
                $(".loader").hide()
                $('.mandatory-field').removeClass('is-invalid');
                $('.mandatory-field').next().html("");
                $.each(data.responseJSON.errors,function(index,value) {
                    var sel = index.split('.')[0]; 
                    var selector = $("#" + sel)

                    selector.addClass('is-invalid');
                    selector.next().html(value[0]);

                })
            }
        });

    })

    $(document).on('click',".remove-product-thumnail-image",function(){
        let product =$(this).data('product');
        if(isNumber(product)) {
            Swal.fire({
                        
                title: 'Are you sure to delete this thumbnail image?',
                text: "",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.post( "/api/delete-thumbnail/",{product:product,file_to_delete:$(this).data('name')},function( data ) {
                        Swal.fire({
                            title: data.message,
                            text:'',
                            icon:data.status,
                        }).then(function (result) {
                            location.reload()
                        });
                    }).fail(function(response) {
                        let data = response.responseJSON
                        Swal.fire({
                            title: data.message,
                            text:'',
                            icon:data.status,
                        }).then(function (result) {
                            location.reload()
                        });
                    })


                } 
            });
            
        }
    });

</script>
@endpush


