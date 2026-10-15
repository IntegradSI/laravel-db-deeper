import {
    Activity,
    Archive,
    CircleAlert,
    CircleCheck,
    CircleHelp,
    Clock3,
    createIcons,
    Database,
    Eye,
    Gauge,
    HardDrive,
    Info,
    Layers3,
    LayoutDashboard,
    Menu,
    ServerCog,
    ShieldCheck,
    TableProperties,
} from 'lucide';

createIcons({
    icons: {
        Activity,
        Archive,
        CircleAlert,
        CircleCheck,
        CircleHelp,
        Clock3,
        Database,
        Eye,
        Gauge,
        HardDrive,
        Info,
        Layers3,
        LayoutDashboard,
        Menu,
        ServerCog,
        ShieldCheck,
        TableProperties,
    },
});

const drawer = document.querySelector('#db-deeper-drawer');

document.querySelectorAll('.drawer-side a').forEach((link) => {
    link.addEventListener('click', () => {
        if (drawer instanceof HTMLInputElement) {
            drawer.checked = false;
        }
    });
});
