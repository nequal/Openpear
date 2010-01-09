<?php
import('org.rhaco.net.xml.Atom');

class Timeline extends OpenpearFlow
{
    function package_timeline($package_name){
        Http::redirect(url('package/'. $package_name));
    }
    
    function atom(){
        Atom::convert('Openpear Timelines', url('timelines.atom'),
            C(OpenpearTimeline)->find_all(new Paginator(20), Q::order('-id'))
        )->output();
    }
    function atom_package($package_name){
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        Atom::convert('Openpear Package Timelines: '. $package->name(), url('timelines.atom'),
            C(OpenpearTimeline)->find_all(new Paginator(20), Q::eq('package_id', $package->id()), Q::order('-id'))
        )->output();
    }
    function atom_maintainer($maintainer_name){
        $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $maintainer_name));
        Atom::convert('Openpear Maintainer Timelines: '. $maintainer->name(), url('timelines.atom'),
            C(OpenpearTimeline)->find_all(new Paginator(20), Q::eq('maintainer_id', $maintainer->id()), Q::order('-id'))
        )->output();
    }
}
