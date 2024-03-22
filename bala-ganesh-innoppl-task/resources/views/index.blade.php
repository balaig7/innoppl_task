@extends('layouts.app')

@section('content')
@push('css')
    <link rel="stylesheet" href="{{asset('css/dataTables.css')}}"/>
    <link href="{{asset('css/sweetalert2.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/fontawesome.min.css')}}" />

@endpush
<div class="text-center loader">
    <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
</div>

<div class="container">
    <div class="card p-3">

        <div class="d-flex flex-row-reverse bd-highlight">
            @if(auth()->user()->role === 'customer')
            <div class="p-2 bd-highlight">
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#importCsv">Import</button>

            </div>

            <div class="p-2 bd-highlight">
                <a href="/product/create" class="btn btn-primary btn-sm">Add New Product</a>

            </div>

            @endif            
            <div class="p-2 bd-highlight">
                <a href="{{url()->current().'/export-product'}}" class="btn btn-warning btn-sm">Export</a>

            </div>

          </div>
          
    </div>
    <div class="card-body">
    <table class="table" id="products">
        <thead>
            <tr>
            <th scope="col">#</th>
            <th scope="col">Product Name</th>
            <th scope="col">Sku</th>
            <th scope="col">Amount</th>
            @if(auth()->user()->role === 'customer')
            <th scope="col">Action</th>
            @endif

            </tr>
        </thead>
        <tbody>
            @foreach ($products as $pKey => $product)
                
            <tr>
                <th scope="row">{{++$pKey}}</th>
                <td>{{$product->name}}</td>
                <td>{{$product->sku}}</td>
                <td>₹{{$product->amount}}</td>
                @if(auth()->user()->role === 'customer')

                <td>
                    <a href="/product/{{$product->id}}/edit" class="btn bnt-sm btn-info"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                    <button type="button" class="btn bnt-sm btn-danger" id="delete-product" data-product="{{$product->id}}"><i class="fa fa-trash" aria-hidden="true"></i></button>
                </td>
                @endif

            </tr>
            @endforeach
            
        </tbody>
    </table>
    </div>
</div>
@if(auth()->user()->role === 'customer')

{{-- Import file --}}
<div class="modal fade" id="importCsv" tabindex="-1" aria-labelledby="importCsvLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="importCsvLabel">Import Csv</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger" style="display: none" role="alert">
            </div>
          <form id="import-csv" method="post">
            <div class="mb-3">
              <label class="col-form-label">CSV File</label>
              <input type="file" class="form-control mandatory-field" id="csv_file" name="csv_file">
              <span class="invalid-feedback" role="alert"></span>
            </div>
            <div class="mb-3">
                Please refer <a href="{{asset('products-sample.csv')}}"> this </a>sample and then import
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" id="import-csv-data" class="btn btn-primary">Import</button>
        </div>
      </div>
    </div>
</div>
@endif

{{-- Import file --}}
@endsection


@push('custom-scripts')
  
    <script src="{{asset('js/dataTables.js')}}"></script>
    <script> 

    $(document).ready(function(){
        $('#products').DataTable();

    })
    </script>
    <script src="{{asset('js/sweetalert2.all.min.js')}}"></script>
    @if(auth()->user()->role === 'customer')

    <script>
        function isNumber(value) {
            return !isNaN(parseFloat(value)) && isFinite(value);
        }
        
        $(document).ready( function () {
            
            $("#import-csv-data").on('click',function(){
                
                var formData = new FormData($("#import-csv")[0]);
                $(".loader").show()
            
                $.ajax({
                    type: "POST",
                    url: '/api/product-import',
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
                            window.location.href = data.redirect_url
                        });
                    },
                    error: function(data) {

                        $(".loader").hide()
                        if(data.status == 400) {
                            $('.alert').show(0).delay(5000).hide(0);
                            var errors = '<ul>'
                            $.each(data.responseJSON.errors,function(index,value) {
                                errors +='<li>'+value+'</li>'
                            })
                            errors +='</ul>';
                            $('.alert').html(errors);
                        }else{
                            $('.mandatory-field').removeClass('is-invalid');
                            $('.mandatory-field').next().html("");
                            $.each(data.responseJSON.errors,function(index,value) {
                                var selector = $("#" + index)
                                selector.addClass('is-invalid');
                                selector.next().html(value[0]);
                
                            })

                        }

                    }
                });

            })


            $(document).on('click',"#delete-product",function(){
                let product =$(this).data('product');

                if(isNumber(product)) {
                    Swal.fire({
                        
                        title: 'Are you sure to delete this product?',
                        text: "",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes'
                    }).then((result) => {

                        if (result.isConfirmed) {

                            $.post( "/api/delete/"+product,function( data ) {
                                Swal.fire({
                                title: data.message,
                                text:'',
                                icon:data.status,
                            }).then(function (result) {
                                    window.location.href = data.redirect_url
                            });
                            });

                        } 
                    });
                    
                }
            });

        });


    </script>
    @endif
  
@endpush


