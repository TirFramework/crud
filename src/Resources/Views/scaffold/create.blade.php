@extends(config('crud.admin-panel').'::layouts.master')
@section('navbar')
    {{--navbar add here--}}
@endsection

@section('title', trans('crud::panel.create').' '.trans("$crud->name::panel.$crud->name") )

@section('page-heading'){{trans('crud::panel.create').' '.trans("$crud->name::panel.$crud->name")}} @endsection

@section('content')
    <div class="card card-default">
        <div class="card-header clearfix">{{trans("crud::panel.inputs")}}</div>
        <div class="card-body">
            {!! Form::open(['route' => "$crud->name.store", 'method' => 'POST', 'class'=>'form-horizontal row', 'enctype'=>'multipart/form-data']) !!}
            {{--Submit & Cancel--}}
            @include("crud::scaffold.inputTypes.save",['crud'=>$crud])
            @foreach($crud->fields as $field)
                @if(strpos($field->visible, 'c') !== false)
                    @if(!isset($field->display))
                        @php $field->display = $field->name; @endphp
                    @endif
                    @if(view()->exists("$crud->name::inputTypes.$field->type"))
                        @include("$crud->name::inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                    @else
                        @include("crud::scaffold.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                    @endif
                @endif
            @endforeach

            {{--Submit & Cancel--}}
            @include("crud::scaffold.inputTypes.save",['crud'=>$crud])
            {!! Form::close() !!}
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        editor();
        $(".form-horizontal").validate({
            onkeyup: true,
            onfocus: true
        });
    </script>
@endpush
