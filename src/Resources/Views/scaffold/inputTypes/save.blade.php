{{--Submit & Cancel--}}
<div class="w-100">
    <div class="form-group text-right m-0">
        {!! Form::label('', '', ['class' => 'control-label']) !!}
        <div class="">
            {!!  Form::submit(trans('crud::panel.create_close'),['name'=>'save_close','class'=>'btn btn-success save_close'])!!}
            {!!  Form::submit(trans('crud::panel.create'),['name'=>'save_edit','class'=>'btn btn-info save'])!!}
            <a type="button" href="{{route("$crud->name.index")}}" class="btn btn-warning">{{trans('crud::panel.cancel')}}</a>
        </div>
    </div>
</div>
