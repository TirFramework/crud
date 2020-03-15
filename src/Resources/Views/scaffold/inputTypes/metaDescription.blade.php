
{{--Meta Description--}}
<div class="form-group">
    {!! Form::label('meta_description', trans("$crud->name::panel.meta_description"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        <div class="panel-group">
            <div class="panel panel-default">
                <div class="panel-heading panel-heading-gray" data-toggle="collapse" href="#collapse_meta">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" href="#collapse_meta">{{trans("$crud->name::panel.meta_description")}} </a>
                    </h4>
                </div>
                <div id="collapse_meta" class="panel-collapse collapse">
                    <div class="panel-body">
                        {{--title--}}
                        <div class="form-group">
                            {!! Form::label("meta_description[keywords]",trans("$crud->name::panel.keywords"), ['class' => 'col-md-2 control-label']) !!}
                            <div class="col-md-10">
                                {!! Form::text("meta_description[keywords]",null,['class' => ' form-control'])!!}
                            </div>
                        </div>

                        {{--description--}}
                        <div class="form-group">
                            {!! Form::label("meta_description[description]",trans("$crud->name::panel.description"), ['class' => 'col-md-2 control-label']) !!}
                            <div class="col-md-10">
                                {!! Form::textarea("meta_description[description]",null,['class' => ' form-control'])!!}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
