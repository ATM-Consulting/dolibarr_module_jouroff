
    [view.head;strconv=no]
	[onshow;block=begin;when [joursFeries.titreAction]=='new']
		[joursFeries.titreCreate;strconv=no;protect=no]
	[onshow;block=end] 
	[onshow;block=begin;when [joursFeries.titreAction]=='view']
		[joursFeries.titreVisu;strconv=no;protect=no]
	[onshow;block=end] 
	
	<table class="border" style="width:30%">
		<tr>
			<td>[translate.NoWorkedDays;strconv=no;protect=no]</td>
			<td>[joursFeries.date_jourOff;strconv=no;protect=no]</td>

		</tr>
		<tr>
			<td>[translate.Period;strconv=no;protect=no]</td>
			<td>[joursFeries.moment;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>[translate.Comment;strconv=no;protect=no]</td>
			<td>[joursFeries.commentaire;strconv=no;protect=no]</td>
		</tr>
	</table>

[onshow;block=begin;when [view.mode]=='view']
		[onshow;block=begin;when [userCourant.droitAjoutJour]=='1']
		<div class="tabsAction" >
		<div  style="text-align:center;">
			<a class="butAction"  href="?&fk_user=[userCourant.id]">[translate.Back;strconv=no;protect=no]</a>
			<a class="butAction"  href="?idJour=[joursFeries.id]&fk_user=[userCourant.id]&action=edit">[translate.Modify;strconv=no;protect=no]</a>
			<a class="butActionDelete" onclick="if (window.confirm('[translate.ConfirmDeletePublicHoliday;strconv=no;protect=no]')){href='?idJour=[joursFeries.id]&fk_user=[userCourant.id]&action=delete'};">[translate.Delete;strconv=no;protect=no]</a>
		</div>
		</div>
		[onshow;block=end] 
[onshow;block=end] 

[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="[translate.Register;strconv=no;protect=no]" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="[translate.Cancel;strconv=no;protect=no]" name="cancel" class="button" onclick="document.location.href='?id=[userCourant.id]'">
	</div>
[onshow;block=end] 

