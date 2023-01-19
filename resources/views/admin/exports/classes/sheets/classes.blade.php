<table>
    <thead>
    <tr>
        <th>id</th>
        <th>name</th>
        <th>damage_stat</th>
        <th>to_hit_stat</th>
        <th>str_mod</th>
        <th>dur_mod</th>
        <th>dex_mod</th>
        <th>chr_mod</th>
        <th>int_mod</th>
        <th>agi_mod</th>
        <th>focus_mod</th>
        <th>accuracy_mod</th>
        <th>dodge_mod</th>
        <th>defense_mod</th>
        <th>looting_mod</th>
        <th>primary_required_class_id</th>
        <th>secondary_required_class_id</th>
        <th>primary_required_class_level</th>
        <th>secondary_required_class_level</th>
    </tr>
    </thead>
    <tbody>
    @foreach($gameClasses as $gameClass)
        <tr>
            <td>{{$gameClass->id}}</td>
            <td>{{$gameClass->name}}</td>
            <td>{{$gameClass->damage_stat}}</td>
            <td>{{$gameClass->to_hit_stat}}</td>
            <td>{{$gameClass->str_mod}}</td>
            <td>{{$gameClass->dur_mod}}</td>
            <td>{{$gameClass->dex_mod}}</td>
            <td>{{$gameClass->chr_mod}}</td>
            <td>{{$gameClass->int_mod}}</td>
            <td>{{$gameClass->agi_mod}}</td>
            <td>{{$gameClass->focus_mod}}</td>
            <td>{{$gameClass->accuracy_mod}}</td>
            <td>{{$gameClass->dodge_mod}}</td>
            <td>{{$gameClass->defense_mod}}</td>
            <td>{{$gameClass->looting_mod}}</td>
            <td>{{$gameClass->primaryClassRequired->name}}</td>
            <td>{{$gameClass->secondaryClassRequired->name}}</td>
            <td>{{$gameClass->primary_required_class_level}}</td>
            <td>{{$gameClass->secondary_required_class_level}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
