{{--Submit & Cancel--}}
<div class="w-100">
    <div class="form-group text-right m-0">
        {!! Form::label('', '', ['class' => 'control-label']) !!}
        <div class="">
            {!!  Form::submit(trans('core::panel.create_close'),['name'=>'save_close','class'=>'btn btn-success save_close'])!!}
            {!!  Form::submit(trans('core::panel.create'),['name'=>'save_edit','class'=>'btn btn-info save'])!!}
            <a type="button" href="{{route("admin.$model->moduleName.index")}}"
               class="btn btn-warning">{{trans('core::panel.cancel')}}</a>
        </div>
    </div>
</div>
