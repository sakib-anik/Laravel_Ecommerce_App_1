@extends('admin.layouts.app')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">					
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Page</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        <form action="" method="post" id="pageForm" name="pageForm">
            
            <div class="card">
                <div class="card-body">								
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input value="{{ $page->name }}" type="text" name="name" id="name" class="form-control" placeholder="Name">	
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <input value="{{ $page->slug }}" type="text" name="slug" id="slug" class="form-control" placeholder="Slug" readonly>	
                                <p></p>
                            </div>
                        </div>		
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="content">Content</label>
                                <textarea name="content" id="content" class="summernote" cols="30" rows="10">{!! $page->content !!}</textarea>
                            </div>								
                        </div>				
                    </div>
                </div>							
            </div>
            <div class="pb-5 pt-3">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('pages.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
            </div>
        </form>
        {{-- <div class="row">
            @foreach ($images as $image)
            <div class="col-sm-4">
                <a href="{{asset('temp/'.$image->name)}}" target="_blank">
                    <img class="" style="width: 30px; height: 20px" src="{{asset('temp/'.$image->name)}}" >
                </a>
            </div>
            @endforeach

        </div> --}}
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->
@endsection
@section('customJs')
    <script>
	$('#pageForm').submit(function(event){
        event.preventDefault();
        var element = $(this);
        $("button[type=submit]").prop('disabled',true);
        $.ajax({
            url: '{{ route("pages.update",$page->id) }}',
            type: 'put',
            data: element.serializeArray(),
            dataType: 'json',
            success: function(response){
                $("button[type=submit]").prop('disabled',false);
                if(response["status"] == true){

                    $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                    $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                    
                    window.location.href="{{ route('pages.index') }}";

                }
                else{
                    var errors = response['errors'];
                    if(errors['name']){
                        $('#name').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['name']);
                    }
                    else{
                        $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                    }
                    if(errors['slug']){
                        $('#slug').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['slug']);
                    }
                    else{
                        $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                    }
                }
            },
            error: function(jqXHR, exception){
                console.log('Something went wrong');
            } 
        });
    });

    $('#name').change(function(){
        element = $(this);
        $("button[type=submit]").prop('disabled',true);
        $.ajax({
            url: '{{ route('getSlug') }}',
            type: 'get',
            data: {title: element.val()},
            dataType: 'json',
            success: function(response){
                $("button[type=submit]").prop('disabled',false);
                if(response["status"] == true){
                    $('#slug').val(response['slug']);
                }
            }
        });
    });
    
    </script>
@endsection