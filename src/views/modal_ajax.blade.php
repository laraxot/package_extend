<div class="modal fade bs-example-modal-lg" id="myModalAjax" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" >Modal title</h4>
			</div>
			<div class="form-msg"></div>
			<div class="modal-body" id="modalbody">
				...
			</div>
			<div class="modal-footer">
				{{-- per farlo funzionare o cambio javascript ed impongo 1 solo form.. che puo' essere, oppure chiudo il form dopo di questo input,ma non mi piace
				perche' preferisco che le cose aperte siano chiuse nello stesso file.
				<input type="submit" class="btn btn-primary" name="submit" value="Update" />&nbsp;
				--}}
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				{{--  <button type="button" class="btn btn-primary">Save changes</button>--}}
			</div>
		</div>
	</div>
</div>
{{ Theme::add('extend::js/modal_ajax.js') }}
{{-- ESEMPIO DI FUNZIONAMENTO
<a data-href="{{ route('trasferte.fuorisede.show',$item->id) }}" class="btn" data-toggle="modal" data-target="#myModalAjax" data-title="trasferta n.{{ $item->id }}">
						<i class="fa fa-eye"></i>
					</a>
 --}}