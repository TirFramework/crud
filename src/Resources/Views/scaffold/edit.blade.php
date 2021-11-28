@extends('first-panel::layouts.master')
@section('navbar')
    {{--navbar add here--}}
@endsection
@php
    use Illuminate\Support\Arr
@endphp
@section('title', trans('core::panel.edit').' '.trans("$model->moduleName::panel.$model->moduleName") )

@section('page-heading'){{trans('core::panel.edit').' '.trans("$model->moduleName::panel.$model->moduleName")}} @endsection

@section('content')

    {!! Form::model($model, ['route'=>["admin.$model->moduleName.update",$model->getKey()],'method' => 'put',
            'class'=>'form-horizontal ', 'enctype'=>'multipart/form-data']) !!}

    <div class="card card-default">
        <div class="card-header d-flex align-items-center">
            @if(view()->exists("$model->moduleName::admin.inputTypes.update"))
                @include("$model->moduleName::admin.inputTypes.update",['model'=>$model])
            @elseif (view()->exists("admin.$model->moduleName.inputTypes.update"))
                @include("admin.$model->moduleName.inputTypes.update",['model'=>$model])
            @else
                @include("core::scaffold.inputTypes.update",['model'=>$model])
            @endif
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="row">
                    @foreach($model->getEditFields() as $field)
                        {{--check local folder have input or no--}}
                        @if (view()->exists("$model->moduleName::admin.inputTypes.$field->type"))
                            @include("$model->moduleName::admin.inputTypes.$field->type",['field'=>$field, 'model'=>$model])
                        @else
                            @include("core::scaffold.inputTypes.$field->type",['field'=>$field, 'model'=>$model])
                        @endif
                    @endforeach
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


    </script>
@endpush
