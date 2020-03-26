@extends('first-panel::layouts.master')
@section('navbar')
    {{--navbar add here--}}
@endsection

@section('title', trans('crud::panel.edit').' '.trans("$crud->name::panel.$crud->name") )

@section('page-heading'){{trans('crud::panel.edit').' '.trans("$crud->name::panel.$crud->name")}} @endsection

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading clearfix">{{trans('crud::panel.inputs')}}</div>
        <div class="panel-body">
            {!! Form::model($item, ['route'=>["$crud->name.update",$item->getKey()],'method' => 'put', 'class'=>'form-horizontal row-border', 'enctype'=>'multipart/form-data']) !!}
            @foreach($crud->fields as $field)
                @if(strpos($field->visible, 'e') !== false)
                    @if(!isset($field->display))
                        @php $field->display = $field->name; @endphp
                    @endif
                    {{--check local folder have input or no--}}
                    @if(view()->exists("$crud->name::admin.inputTypes.$field->type"))
                            @include("$crud->name::admin.inputTypes.$field->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
                        @else
                            @include("crud::scaffold.inputTypes.$field->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
                    @endif
                @endif
            @endforeach

            {{--Submit & Cancel--}}
            @if(view()->exists("$crud->name::admin.inputTypes.update"))
                @include("$crud->name::admin.inputTypes.update",['crud'=>$crud])
            @else
                @include("crud::scaffold.inputTypes.update",['crud'=>$crud])
            @endif

            {!! Form::close() !!}
        </div>
    </div>

    {{-- Load additional fields --}}
    @foreach ($crud->additionalFields as $aField)
        @if(strpos($aField->visible, 'e') !== false)
            @if(!isset($aField->display))
                @php $aField->display = $aField->name; @endphp
            @endif
            {{--check local folder have input or no--}}
            @if(view()->exists("$crud->name::admin.inputTypes.$aField->type"))
                    @include("$crud->name::admin.inputTypes.$aField->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
                @else
                    @include("crud::scaffold.inputTypes.$aField->type",['field'=>$aField,'crud'=>$crud, 'item'=>$item])
            @endif
        @endif
    @endforeach

    
@endsection


@push('scripts')
    <script>
        editor();
    </script>
@endpush

