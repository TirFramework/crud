@extends(config('crud.admin-panel').'::layouts.master')
@section('navbar')
    {{--navbar add here--}}
@endsection

@section('title', trans('core::panel.create').' '.trans("$model->moduleName::panel.$model->moduleName") )

@section('page-heading'){{trans('core::panel.create').' '.trans("$model->moduleName::panel.$model->moduleName")}} @endsection

@section('content')
    {!! Form::open(['route' => "admin.$model->moduleName.store", 'method' => 'POST', 'class'=>'form-horizontal', 'enctype'=>'multipart/form-data']) !!}

    <div class="row">

        <div class="col-md-12 col-12">

            <div class="card card-default">
                <div class="card-header d-flex align-items-center">

                    {{--Submit & Cancel--}}
                    @if(view()->exists("$model->moduleName::admin.inputTypes.save"))
                        @include("$model->moduleName::admin.inputTypes.save",['model'=>$model])
                    @elseif (view()->exists("admin.$model->moduleName.inputTypes.save"))
                        @include("admin.$model->moduleName.inputTypes.save",['model'=>$model])
                    @else
                        @include("core::scaffold.inputTypes.save",['model'=>$model])
                    @endif


                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($model->getCreateFields() as $field)
                            {{--check local folder have input or no--}}
                            @if(view()->exists("admin.$model->moduleName.inputTypes.$field->type"))
                                @include("admin.$model->moduleName.inputTypes.$field->type",['field'=>$field,'model'=>$model])
                            @elseif (view()->exists("$model->moduleName::admin.inputTypes.$field->type"))
                                @include("$model->moduleName::admin.inputTypes.$field->type",['field'=>$field,'model'=>$model])
                            @else
                                @include("core::scaffold.inputTypes.$field->type",['field'=>$field,'model'=>$model])
                            @endif
                        @endforeach
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
