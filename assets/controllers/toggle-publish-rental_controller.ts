import { Controller } from '@hotwired/stimulus';
import axios from 'axios';
import Swal from 'sweetalert2'

export default class extends Controller {
    static values = {
        apiUri: String,
    }

    static targets = ['input']

    private apiUriValue: string;

    private inputTarget: HTMLInputElement;

    public async connect() {
    }

    public async togglePublishRental(ev: Event): Promise<void> {
        ev.preventDefault();
        let result = { isConfirmed: true }
        let isDisabled = false;

        if (!this.inputTarget.checked) {
            result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Si vous masquez votre annonces, elle ne sera plus disponible dans les résultats de recherche.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, masquer mon annonce',
                cancelButtonText: 'Annuler',
            });
            isDisabled = true;
        }
        if (!result.isConfirmed && isDisabled) {
            this.inputTarget.checked = true;
            return;
        }

        let res = await axios.patch(this.apiUriValue, null, { validateStatus: null });
        if (200 === res.status) {
            await Swal.fire(
                isDisabled ? 'Annonce masquée' : 'Annonce publiée',
                isDisabled ? 'À tout moment, vous pouvez la publier à nouveau.' : 'Elle est désormais visible dans la recherche.',
                'success'
            );
        }

        if (403 === res.status) {
            await Swal.fire(
                'Action impossible',
                res.data.message,
                'error'
            );
            this.inputTarget.checked = false;
        }
    }
}