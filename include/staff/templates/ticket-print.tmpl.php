<html>

<head>
    <style type="text/css">
@page {
    header: html_def;
    footer: html_def;
    margin: 15mm;
    margin-top: 30mm;
    margin-bottom: 22mm;
}
.logo {
  max-width: 220px;
  max-height: 71px;
  width: auto;
  height: auto;
  margin: 0;
}
#ticket_thread .message,
#ticket_thread .response,
#ticket_thread .note {
    margin-top:10px;
    border:1px solid #aaa;
    border-bottom:2px solid #aaa;
}
#ticket_thread .header {
    text-align:left;
    border-bottom:1px solid #aaa;
    padding:3px;
    width: 100%;
    table-layout: fixed;
}
#ticket_thread .message .header {
    background:#C3D9FF;
}
#ticket_thread .response .header {
    background:#FFE0B3;
}
#ticket_thread .note .header {
    background:#FFE;
}
#ticket_thread .info {
    padding:5px;
    background: snow;
    border-top: 0.3mm solid #ccc;
}

table.meta-data {
    width: 100%;
}
table.custom-data {
    margin-top: 10px;
}
table.custom-data th {
    width: 25%;
}
table.custom-data th,
table.meta-data th {
    text-align: right;
    padding: 3px 8px;
}
table.meta-data td {
    padding: 3px 8px;
}
.faded {
    color:#666;
}
.pull-left {
    float: left;
}
.pull-right {
    float: right;
}
.flush-right {
    text-align: right;
}
.flush-left {
    text-align: left;
}
.ltr {
    direction: ltr;
    unicode-bidi: embed;
}
.headline {
    border-bottom: 2px solid black;
    font-weight: bold;
}
div.hr {
    border-top: 0.2mm solid #bbb;
    margin: 0.5mm 0;
    font-size: 0.0001em;
}
.thread-entry, .thread-body {
    page-break-inside: avoid;
}
<?php include ROOT_DIR . 'css/thread.css'; ?>
    </style>
</head>
<body>

<htmlpageheader name="def" style="display:none">
<?php if ($logo = $cfg->getClientLogo()) { ?>
    <img src="cid:<?php echo $logo->getKey(); ?>" class="logo"/>
<?php } else { ?>
    <img src="<?php echo INCLUDE_DIR . 'fpdf/print-logo.png'; ?>" class="logo"/>
<?php } ?>
    <div class="hr">&nbsp;</div>
    <table><tr>
        <td class="flush-left"><?php echo (string) $ost->company; ?></td>
    </tr></table>
</htmlpageheader>

<htmlpagefooter name="def" style="display:none">
    <div class="hr">&nbsp;</div>
    <table width="100%"><tr><td class="flush-left">
        Chamado #<?php echo $ticket->getNumber(); ?> impresso por
        <?php echo $thisstaff->getUserName(); ?> em
        <?php echo Format::daydatetime(Misc::gmtime()); ?>
    </td>
    <td class="flush-right">
        Página {PAGENO}
    </td>
    </tr></table>
</htmlpagefooter>

<!-- Ticket metadata -->
<h1>Chamado #<?php echo $ticket->getNumber(); ?></h1>
<table class="meta-data" cellpadding="0" cellspacing="0"  width="100%" border="0">
<tbody>
<tr>
    <th align="left" width="30%"><?php echo __('Department'); ?><?php echo ' :'; ?></th>
    <td><?php echo $ticket->getDept(); ?></td>
</tr>
</tbody>
</table>

<!-- Custom Data -->
<?php
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
    // Skip core fields shown earlier in the ticket view
    $answers = $form->getAnswers()->exclude(Q::any(array(
        'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
        'field__name__in' => array('subject', 'priority')
    )));
    if (count($answers) == 0)
        continue;
    ?>
        <table class="meta-data" cellspacing="0" cellpadding="0" width="100%" border="0">
        <?php foreach($answers as $a) {
            if (!($v = $a->display())) continue; ?>
            <tr>
                <th align="left" width="30%"><?php echo $a->getField()->get('label');?>:</th>
                <td><?php echo $v;?></td>
            </tr>
            <?php } ?>
        </table>
    <?php
    $idx++;
} ?>

<table class="meta-data" cellpadding="0" cellspacing="0"  width="100%" border="0">
<tbody>
<tr>
    <th align="left"><?php echo __('Name'); ?><?php echo ' :'; ?></th>
    <td><?php echo $ticket->getOwner()->getName(); ?></td>
</tr>
<tr>
    <th align="left"><?php echo __('Email'); ?><?php echo ' :'; ?></th>
    <td><?php echo $ticket->getEmail(); ?></td>
</tr>
<tr>
    <th align="left" width="30%"><?php echo __('Phone'); ?><?php echo ' :'; ?></th>
    <td><?php echo $ticket->getPhoneNumber(); ?></td>
</tr>
<tr>
    <th align="left"><?php echo __('Source'); ?><?php echo ' :'; ?></th>
    <td><?php echo $ticket->getSource(); ?></td>
</tr>
<tr>
    <th align="left"><?php echo __('Create Date'); ?><?php echo ' :'; ?></th>
    <td><?php echo Format::datetime($ticket->getCreateDate()); ?></td>
</tr>
<tr>
    <th align="left"><?php echo __('Help Topic'); ?><?php echo ' :'; ?></th>
    <td><?php echo $ticket->getHelpTopic(); ?></td>
</tr>
</tbody>
</table>



<!-- Ticket Thread -->
<div id="ticket_thread">
<?php
$types = array('M', 'R');
if ($this->includenotes)
    $types[] = 'N';

if ($thread = $ticket->getThreadEntries($types)) {
    $threadTypes=array('M'=>'message','R'=>'response', 'N'=>'note');
    foreach ($thread as $entry) { ?>
        <div class="thread-entry <?php echo $threadTypes[$entry->type]; ?>">
            <table class="header" style="width:100%"><tr><td>
                    <span><?php
                        echo Format::datetime($entry->created);?></span>
                    <span style="padding:0 1em" class="faded title"><?php
                        echo Format::truncate($entry->title, 100); ?></span>
                </td>
                <td class="flush-right faded title" style="white-space:no-wrap">
                    <?php
                        echo Format::htmlchars($entry->getName()); ?></span>
                </td>
            </tr></table>
            <div class="thread-body">
                <div><?php echo $entry->getBody()->display('pdf'); ?></div>
            <?php
            if ($entry->has_attachments
                    && ($files = $entry->attachments)) { ?>
                <div class="info">
<?php           foreach ($files as $A) { ?>
                    <div>
                        <span><?php echo Format::htmlchars($A->file->name); ?></span>
                        <span class="faded">(<?php echo Format::file_size($A->file->size); ?>)</span>
                    </div>
<?php           } ?>
                </div>
<?php       } ?>
            </div>
        </div>
<?php }
} ?>
</div>

<p ><?php echo "Resolução: ____________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________"; ?></p>
</br>
<h3><?php echo "TERMO DE RESPONSABILIDADE"; ?></h3>
<p ><?php echo "Caso seja solicitada a formatação do equipamento. o solicitante fica ciente que este procedimento apaga definitivamente todos os dados do computador. Por esse termo, assume que foi verificado backup, portanto, responsabiliza-se por toda e qualquer informação removida, incluindo informações de outros perfis do computador."; ?></p>
</br>

<table border="10" width="100%">
<tr>
<td><p><?php echo "____________________________________"; ?></p></td>
<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
<td><p align="right"><?php echo "_____________________________________ "; ?></p></td>
</tr>
<tr>
<td><p><?php echo "Assinatura do Solicitante "; ?></p></td>
<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
<td><p align="right"><?php echo "Assinatura do Responsável Técnico"; ?></p></td>
</tr>
</table>
</br>
</br>
<p align="center"><?php echo "Paulista, ___________ de _________________________ de 2017"; ?></p>
</body>
</html>
