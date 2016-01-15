<?php
echo <<<ROWDATA
<tr class="ticket- $tclass">
	<td>$tn
		<a href="/ticket/edit/$tid/" class="btn btn-xs btn-default" title="Открыть заявку">Открыть</a>
	</td>
	<td>$dcreate</td>
	<td>$tdepartment</td>
	<td>$twork</td>
	<td>$tnode</td>
	<td>$tstatus</td>
</tr>

ROWDATA;
