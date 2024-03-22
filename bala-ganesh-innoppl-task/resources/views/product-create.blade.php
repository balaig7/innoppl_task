@extends('layouts.app')

@section('content')
<div class="text-center loader">
    <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
</div>
<div class="container">
    <div class="card  p-3 shadow  mb-5 bg-body rounded">
        <div class="d-flex flex-row-reverse bd-highlight">
            <div class="p-2 bd-highlight">
                <a href="/" class="btn btn-danger float-end">Back</a>
            </div>
        </div>


        <div class="card-header p-3 my-3 bg-primary text-white">Add New Product</div>
        <form method="post" id="create-product">
            <div class="mb-3">
                <label for="productName" class="form-label">Name<span class="text-danger">*</span></label>
                <input type="text" class="form-control mandatory-field" id="product_name" name="product_name">
                <span class="invalid-feedback" role="alert"></span>
            </div>
            <div class="mb-3">
                <label for="productPrice" class="form-label">Price<span class="text-danger">*</span></label>
                <input type="text" name="price" id="price" placeholder="0.00" class="form-control mandatory-field">
                <span class="invalid-feedback" role="alert"></span>
            </div>
            <div class="mb-3">

                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" id="description" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Upload Images<span class="text-danger">*</span></label>
                <input type="file" class="form-control mandatory-field" id="images" name="images[]" multiple>
                <span class="invalid-feedback" role="alert"></span>
            </div>

            <button class="btn btn-primary" id="save-product" type="button">Save</button>
        </form>
    </div>
</div>

@endsection
@push('css')
    <link href="{{asset('css/sweetalert2.min.css')}}" rel="stylesheet">

@push('scripts')
    <script src="{{asset('js/sweetalert2.all.min.js')}}"></script>
    <script src="{{asset('ckeditor/ckeditor.js')}}"></script>
@endpush
@push('custom-scripts')

<script>

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
    $(document).on("click","#save-product",function(){

        var formData = new FormData($("#create-product")[0]);
        $(".loader").show()

        $.ajax({
            type: "POST",
            url: '/api/product',
            data: formData,
            cache:false,
            contentType: false,
            processData: false,
            success: function(data) {
                $(".loader").hide();

                Swal.fire({
                    title: data.message,
                    text:'',
                    icon:data.status,
                }).then(function (result) {
                    window.location.href = data.redirect_url
                });
            },
            error: function(data) {
                $(".loader").hide();

                if(data.status == 500) {
                    Swal.fire({
                        title: data.message,
                        text:'',
                        icon:data.status,
                    }).then(function (result) {
                        location.reload()
                    });
                } else {

                    $('.mandatory-field').removeClass('is-invalid');
                    $('.mandatory-field').next().html("");
                    $.each(data.responseJSON.errors,function(index,value) {
                        var sel = index.split('.')[0]; 
                        var selector = $("#" + sel)
                        selector.addClass('is-invalid');
                        selector.next().html(value[0]);
    
                    })
                }
            }
        });

    })
</script>
@endpush


