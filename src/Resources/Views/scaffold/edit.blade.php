@extends('first-panel::layouts.master')
@section('navbar')
    {{--navbar add here--}}
@endsection

@php
    use Illuminate\Support\Arr
@endphp

@section('title', trans('crud::panel.edit').' '.trans("$crud->name::panel.$crud->name") )

@section('page-heading'){{trans('crud::panel.edit').' '.trans("$crud->name::panel.$crud->name")}} @endsection

@section('content')


    {!! Form::model($item, ['route'=>["admin.$crud->name.update",$item->getKey()],'method' => 'put',
            'class'=>'form-horizontal ', 'enctype'=>'multipart/form-data']) !!}

    <div class="card card-default">
        <div class="card-header d-flex align-items-center">

            @if(view()->exists("$crud->name::admin.inputTypes.update"))
                @include("$crud->name::admin.inputTypes.update",['crud'=>$crud])
            @elseif (view()->exists("admin.$crud->name.inputTypes.update"))
                @include("admin.$crud->name.inputTypes.update",['crud'=>$crud])
            @else
                @include("crud::scaffold.inputTypes.update",['crud'=>$crud])
            @endif
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="row">
                    @foreach($crud->editFields as $field)
                        {{--check local folder have input or no--}}
                        @if(view()->exists("admin.$crud->name.inputTypes.$field->type"))
                            @include("admin.$crud->name.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                        @elseif (view()->exists("$crud->name::admin.inputTypes.$field->type"))
                            @include("$crud->name::admin.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                        @else
                            @include("crud::scaffold.inputTypes.$field->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
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
