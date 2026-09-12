import { Controller } from '@hotwired/stimulus';
import { sleep } from '@crozon/utils';
import axios from 'axios';

export default class extends Controller {
    static values = { api: String };

    private apiValue: string;

    async connect() {
        let { status} = await axios.get(this.apiValue);
        while(204 === status) {
            await sleep(1000);
            let res = await axios.get(this.apiValue);
            status = res.status;
        }

        await window.location.reload();
    }
}