import { Component } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiService } from "./services/api.service";
import { NgxUiLoaderService } from "ngx-ui-loader";
import { ToastrService } from 'ngx-toastr';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.css']
})
export class AppComponent {
    // Define the variables
    clientForm!: FormGroup;
    FutureDate = new Date();
    minDate = this.FutureDate.setDate(this.FutureDate.getDate() + 1);
    form_details: any | undefined = [];
    profile_pic: any;

    constructor(private _fb: FormBuilder, 
        private _apiService: ApiService, 
        private ngxService: NgxUiLoaderService, 
        private toastr: ToastrService
    ) {
        this.buildForm();
    }

    ngOnInit(): void {}

    // Generate a form with validation
    private buildForm(): void {
        this.clientForm = this._fb.group({
            name: ['', [
                Validators.required,
                Validators.minLength(3),
                Validators.maxLength(50)
            ]],
            email: ['', [
                Validators.required,
                Validators.email,
                Validators.minLength(5),
                Validators.maxLength(50)
            ]],
            address: ['', [
                Validators.required,
                Validators.minLength(10),
                Validators.maxLength(100)
            ]],
            phone: ['', [
                Validators.required,
                Validators.pattern("^[0-9\+\-\s]{10,15}$")
            ]],
            delivery_date: ['', [
            ]],
            delivery_time: ['', [
            ]],
            gift_wrap: [false, [
            ]],
            payment_type: ['', [
                Validators.required
            ]],
            suggestion_image: [null, []],
            terms_and_conditions: [, [
                Validators.required,
            ]],
        });
    }

    // Handle form submission
    form_submit() {
        // Create a formdata request and add params to that
        let data = new FormData();
        for (let key in this.clientForm.value) {
            if (key === 'delivery_date') {
                let date_of_birth = new Date(this.clientForm.get('delivery_date')?.value);
                data.append(key, date_of_birth.toISOString().split('T')[0]);
            }
            else if (key === 'delivery_time') {
                let birth_time = new Date(this.clientForm.get('delivery_time')?.value);
                let formatted_birth_time = ("0" + birth_time.getHours()).slice(-2) + ":" + ("0" + birth_time.getMinutes()).slice(-2) + ":00";
                data.append(key, formatted_birth_time);
            }
            else if (key === 'gift_wrap') {
                data.append(key, this.clientForm.get('gift_wrap')?.value ? '1' : '0');
            }
            else if (key === 'suggestion_image') {
                data.append('suggestion_image', this.profile_pic);
            }
            else {
                data.append(key, this.clientForm.get(key)?.value);
            }
        }

        // Start loader
        this.ngxService.start();

        // API call to save data
        this._apiService.create(data).subscribe((res: any) => {
            this.ngxService.stop();  // hide loader
            if (res['STATUS']) {
                this.form_details = res['DATA']['form_data'];
                this.toastr.success(res['MESSAGE'], 'Success');
                this.clientForm.reset();
            }
            else {
                this.toastr.success(res['MESSAGE'], 'Error');
            }
        }, (error) => {
            // Handle API exception
            this.ngxService.stop();
            if (error.hasOwnProperty('error')) {
                this.toastr.error(error['error']['DATA']['error'], 'Error');
            }
            else {
                this.toastr.error(error['statusText'], 'Error');
            }
        });
    }

    // Handle the uplodad profile and validate it
    public validateImage(event: any) {
        this.profile_pic = event.target.files[0];

        if (this.profile_pic != undefined || this.profile_pic != null ) {
            var allowedExtensions = new RegExp("(.*?)\.(jpeg|jpg|png)$")
            if (!(allowedExtensions.test(this.profile_pic.type))) {
                this.clientForm.controls['suggestion_image'].setErrors({ 'type': 'jpeg, jpg, png' });
            }
            else if (this.profile_pic.size > 2048000) {
                this.clientForm.controls['suggestion_image'].setErrors({ 'size': '2048' });
            }
        }

    }

}
