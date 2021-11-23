{{-- RELATÓRIO GERENCIAL TOOL BAR --}}

<DIV CLASS='report-tool-bar'>

<BUTTON TYPE='button' CLASS='btn btn-secondary report-tool m-1' data-toggle='collapse' data-target='#report-selection' aria-expanded='true' aria-controls='report-selection'> <SPAN CLASS='fa fa-filter fa-2x p-2'></SPAN> </BUTTON>
<BUTTON TYPE='button' CLASS='btn btn-secondary report-tool m-1' data-action='print' data-target='#report-area'> <SPAN CLASS='fa fa-print fa-2x p-2'></SPAN> </BUTTON>

{{-- CSV EXPORT --}}
<BUTTON TYPE='button' CLASS='btn btn-secondary report-tool m-1' ONCLICK='$("#csvExport").submit()'> <SPAN CLASS='fa fa-file-csv fa-2x p-2'></SPAN> </BUTTON>

</DIV>

<FORM ID="csvExport" ACTION="{{route('csvExport')}}" METHOD="POST" TARGET="_blank">
    <INPUT TYPE="hidden" NAME="csvFileName" ID="csvFileName"    VALUE="{{$configData->reportConfigFile}}" />
    <INPUT TYPE="hidden" NAME="csvData"     ID="csvData"        VALUE="{{$csvData}}" />
    @csrf
</FORM>