<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
            <a href="{{ route($field->name, $item->slug) }}" target="_blank" class="btn btn-info">@lang("crud::panel.preview")</a>
    </div>
</div>
