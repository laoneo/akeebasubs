<?php
/**
 * @package   AkeebaSubs
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Subscriptions\Admin\Model\EmailTemplates;
use FOF30\Utils\FEFHelper\BrowseView;
use FOF30\Utils\SelectOptions;

defined('_JEXEC') or die();

/**
 * @var  FOF30\View\DataView\Html $this
 * @var  EmailTemplates           $row
 * @var  EmailTemplates           $model
 */

$model = $this->getModel();
$emailKeys = \Akeeba\Subscriptions\Admin\Helper\Email::getEmailKeys(1);
?>

@extends('admin:com_akeebasubs/Common/browse')

@section('browse-filters')
    <div class="akeeba-filter-element akeeba-form-group">
        {{ BrowseView::modelFilter('subscription_level_id', 'title', 'Levels')  }}
    </div>
    <div class="akeeba-filter-element akeeba-form-group">
        @selectfilter('language', SelectOptions::getOptions('languages'))
    </div>
    <div class="akeeba-filter-element akeeba-form-group">
        @selectfilter('key', $emailKeys)
    </div>
    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('subject')
    </div>
    <div class="akeeba-filter-element akeeba-form-group">
        {{ BrowseView::publishedFilter('enabled', 'JENABLED') }}
    </div>
@stop

@section('browse-table-header')
{{-- ### HEADER ROW ### --}}
<tr>
    {{-- Drag'n'drop reordering --}}
    <th width="20">
        @jhtml('FEFHelper.browse.orderfield', 'ordering')
    </th>
    {{-- Row select --}}
    <th width="20">
        @jhtml('FEFHelper.browse.checkall')
    </th>
    {{-- Level --}}
    <th>
        @sortgrid('subscription_level_id')
    </th>
    {{-- Language --}}
    <th>
        @sortgrid('language')
    </th>
    {{-- Key --}}
    <th>
        @sortgrid('key')
    </th>
    {{-- Subject --}}
    <th>
        @sortgrid('subject')
    </th>
    {{-- Enabled --}}
    <th width="60">
        @sortgrid('enabled', 'JENABLED')
    </th>
</tr>
@stop

@section('browse-table-body-withrecords')
{{-- Table body shown when records are present. --}}
<?php $i = 0; ?>
@foreach($this->items as $row)
    <tr>
        {{-- Drag'n'drop reordering --}}
        <td>
            @jhtml('FEFHelper.browse.order', 'ordering', $row->ordering)
        </td>
        {{-- Row select --}}
        <td>
            @jhtml('FEFHelper.browse.id', ++$i, $row->getId())
        </td>
        {{-- Level --}}
        <td>
            {{{ \FOF30\Utils\FEFHelper\BrowseView::modelOptionName($row->subscription_level_id, 'Levels', ['none' => 'COM_AKEEBASUBS_EMAILTEMPLATES_FIELD_SUBSCRIPTION_LEVEL_ID_NONE']) }}}
        </td>
        {{-- Language --}}
        <td>
            {{{ BrowseView::getOptionName($row->language, \FOF30\Utils\SelectOptions::getOptions('languages', ['none' => 'COM_AKEEBASUBS_EMAILTEMPLATES_FIELD_LANGUAGE_ALL'])) }}}
        </td>
        {{-- Key --}}
        <td>
            <a href="@route(BrowseView::parseFieldTags('index.php?option=com_akeebasubs&view=EmailTemplates&task=edit&id=[ITEM:ID]', $row))">
                {{{ BrowseView::getOptionName($row->getFieldValue('key'), $emailKeys) }}}
            </a>
            <br/>
            <small>( <em>{{{ $row->key }}}</em> )</small>
        </td>
        {{-- Subject --}}
        <td>
            <a href="@route(BrowseView::parseFieldTags('index.php?option=com_akeebasubs&view=EmailTemplates&task=edit&id=[ITEM:ID]', $row))">
                {{{ $row->subject }}}
            </a>
        </td>
        {{-- Enabled --}}
        <td>
            @jhtml('FEFHelper.browse.published', $row->enabled, $i)
        </td>
    </tr>
@endforeach
@stop
