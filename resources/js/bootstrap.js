import "bootstrap";

import axios from "axios";
import Swal from "sweetalert2";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

window.Swal = Swal;

const Alert = Swal.mixin({
    customClass: {
        confirmButton: "btn btn-primary mx-1",
        cancelButton: "btn btn-light mx-1",
    },
    buttonsStyling: false,
});

window.Alert = Alert;
