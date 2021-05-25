@extends(config('crud.admin-panel').'::layouts.master')
@section('navbar')
    {{--navbar add here--}}
@endsection

@section('title', trans('crud::panel.create').' '.trans("$crud->name::panel.$crud->name") )

@section('page-heading'){{trans('crud::panel.create').' '.trans("$crud->name::panel.$crud->name")}} @endsection

@section('content')
{!! Form::open(['route' => "$crud->name.store", 'method' => 'POST', 'class'=>'form-horizontal', 'enctype'=>'multipart/form-data']) !!}

    <div class="row">

        <div class="col-md-12 col-12">

            <div class="card card-default">
                <div class="card-header d-flex align-items-center">

                    {{--Submit & Cancel--}}
                    @if(view()->exists("$crud->name::admin.inputTypes.save"))
                        @include("$crud->name::admin.inputTypes.save",['crud'=>$crud])
                    @elseif (view()->exists("admin.$crud->name.inputTypes.save"))
                        @include("admin.$crud->name.inputTypes.save",['crud'=>$crud])
                    @else
                        @include("crud::scaffold.inputTypes.save",['crud'=>$crud])
                    @endif


                </div>
                <div class="card-body">
                        <div class="row">
                            @foreach($crud->createFields as $field)
                                     {{--check local folder have input or no--}}
                                    @if(view()->exists("admin.$crud->name.inputTypes.$field->type"))
                                        @include("admin.$crud->name.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                                    @elseif (view()->exists("$crud->name::admin.inputTypes.$field->type"))
                                        @include("$crud->name::admin.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                                    @else
                                        @include("crud::scaffold.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                                    @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
    {!! Form::close() !!}


@endsection

@push('scripts')
    <script>
        editor();
        $(".form-horizontal").validate({
            lang: $('html').attr('lang'),
            ignore: [],
            onkeyup: true,
            onfocus: true
        });



        //tab && group management
        $('.nav-link').click(function () {
            var id = $(this).attr('href');
            activeingTab(id);
        });

        function activeingTab(element) {
            $('.nav-link').removeClass('active');
            $('a[href="'+element+'"]').addClass('active');

            $('.tab-content .tab-pane').removeClass('active show');
            $(element).addClass('active show');
        }

        // var id = location.hash;
        var id = $('.nav-link').first().attr('href');
        activeingTab(id);

    </script>
@endpush
