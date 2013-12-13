<header class="jumbotron subhead">
<h1>Category Editor</h1>
</header>

<ul class="breadcrumb">
	<li><a href="/categories">Back to main categories and nominations page</a></li>
</ul>

<if:editing>
<!-- <div class="page-header">
	<h1>Editing category:</h1>
</div> -->

<if:editFormError>
<div class="alert alert-error">
	<tag:editFormError />
</div>
</if:editFormError>

<form method="POST" action="/categories/edit" class="form-horizontal well" id="categoryForm">
<input type="hidden" name="action" value="edit" />
<div class="row">
		<div class="span6">
			
			<input type="hidden" name="ID" value="<tag:ID />" />
			
			<div class="control-group">
				<label class="control-label" for="input01">ID</label>
				<div class="controls">
					<input type="text" class="input-large" id="input01" disabled value="<tag:ID />">
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="input02">Name</label>
				<div class="controls">
					<input type="text" class="input-large" id="input02" required name="Name" value="<tag:Name />">
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="input03">Subtitle</label>
				<div class="controls">
					<input type="text" class="input-large" id="input03" required name="Subtitle" value="<tag:Subtitle />">
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="input05">Extra Comments</label>
				<div class="controls">
					<input type="text" class="input-large email" id="input05" name="Comments" value="<tag:Comments />">
				</div>
			</div>
			
		</div>
		
		<div class="span5">
			
			<div class="control-group">
				<label class="control-label" for="input04"><abbr title="Categories with a lower position will be sorted first">Position number</abbr></label>
				<div class="controls">
					<input type="text" class="input-small" id="input04" required name="Order" value="<tag:Order />">
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="Enabled">Category enabled?</label>
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" id="Enabled" value="true" name="Enabled" <tag:Enabled />>
					</label>
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="NominationsEnabled">Nominations enabled?</label>
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" id="NominationsEnabled" value="true" name="NominationsEnabled" <tag:NominationsEnabled />>
					</label>
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="Secret"><abbr title="Secret categories will only show up during the voting stage">Secret category?</abbr></label>
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" id="Secret" value="true" name="Secret" <tag:Secret />>
					</label>
				</div>
			</div>
			
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary">Save changes</button>
					<a href="/categories/edit" class="btn">Cancel</a>
				</div>
			</div>
		</div>
	
	
</div>

</form>

<form method="POST" action="/categories/edit">
	<div class="alert alert-danger">
		<input type="hidden" id="delete" name="delete" value="delete" />
		<button class="btn btn-danger" title="Remove category" type="submit" name="category" value="<tag:ID />">Delete category</button>
	</div>
</form>

</if:editing>

<if:formSuccess>
<div class="alert alert-success">
	<tag:formSuccess />
</div>
</if:formSuccess>

<if:formError>
<div class="alert alert-error">
	<tag:formError />
</div>
</if:formError>

<if:confirmDeletion>
<form method="POST" action="/categories/edit">
<input type="hidden" name="delete" value="delete" />
<input type="hidden" name="category" value="<tag:confirmDeletion />" />
<input type="hidden" name="confirm" value="confirm" />
<div class="alert alert-warning">
	<p>Are you sure you want to delete the category "<tag:confirmDeletion />"?</p>
	<input type="submit" value="Confirm Deletion" class="btn btn-warning" />
</div>
</form>
</if:confirmDeletion>

<table class="table table-bordered table-striped form-table" id="categories">
	<thead>
		<tr>
			<th class="span1">&nbsp;</th>
			<th>ID</th>
			<th>Name</th>
			<th>Subtitle</th>
			<th style="width: 60px;">Order</th>
			<th style="width: 80px;">Controls</th>
		</tr>
	</thead>
	<tbody>
		<form method="POST" action="/categories/edit">
		<input type="hidden" id="delete" name="delete" value="delete" />
		<loop:cats>
		<tr class="<tag:cats[].Class />">
			<td><tag:cats[].Enabled /></td>
			<td><tag:cats[].ID /></td>
			<td><tag:cats[].Name /></td>
			<td><tag:cats[].Subtitle /></td>
			<td><tag:cats[].Order /></td>
			<td>
				<a class="btn" href="/categories/edit/<tag:cats[].ID />" title="Edit category"><i class="icon-pencil"></i> Edit</a>
			</td>
		</tr>
		</loop:cats>
		</form>
		<form method="POST" action="/categories/edit">
			<input type="hidden" name="action" value="new" />
			<tr>
				<td><input type="checkbox" checked name="enabled" id="enabled" /></td>
				<td><input type="text" name="id" id="id" placeholder="ID" style="width: 90%;" maxlength="30" required /></td>
				<td><input type="text" name="name" id="name" placeholder="Name" style="width: 90%;" required /></td>
				<td><input type="text" name="subtitle" id="subtitle" placeholder="Subtitle" style="width: 90%;" required /></td>
				<td><input type="text" name="order" id="order" placeholder="Order" style="width: 90%;" required /></td>
				<td><input type="submit" class="btn" /></div></td>
			</tr>
		</form>
	</tbody>
		
</table>

<form method="POST" action="/categories/edit" id="massChangeNominations">
	<div class="alert alert-info">
		<input type="hidden" id="action" name="action" value="massChangeNominations" />
		<button class="btn" type="submit" name="todo" value="open">Open all nominations</button>
		<button class="btn" type="submit" name="todo" value="close">Close all nominations</button>
	</div>
</form>

<!-- <a class="btn" onclick="addCategory();"><i class="icon-plus-sign"></i> Add new category</a> -->

<script>
$("#massChangeNominations").submit(function() {
  event.preventDefault();
  if(confirm("Are you sure you want to fuck shit up?")) {
    $(this).unbind("submit").submit();
  }
});
</script>