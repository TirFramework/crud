@extends('admin.layouts.master')
@section('navbar')
    {{--navbar add here--}}
@endsection

@section('title', trans('core::panel.show').' '.trans("$crud->name::panel.$crud->name") )

@section('page-heading'){{trans('core::panel.show').' '.trans("$crud->name::panel.$crud->name")}} @endsection

@section('content')
    <div class="panel panel-default view">
        <div class="panel-heading clearfix">{{trans('core::panel.inputs')}}</div>
        <div class="panel-body">
            @foreach($crud->fields as $field)
                @if(strpos($field->visible, 's') !== false)
                    @if(!isset($field->display))
                        @php $field->display = $field->name; @endphp
                    @endif
                    {{--check local folder have input or no--}}
                    @if(view()->exists("$crud->name::admin.inputTypes.$field->type"))
                        @include("$crud->name::admin.inputTypes.$field->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
                    @else
                        @include("core::admin.scaffold.inputTypes.$field->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
                    @endif
                @endif
            @endforeach

        </div>
    </div>
@endsection


@push('scripts')
    <script>
        viewor();
    </script>
@endpush

