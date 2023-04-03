import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs/internal/Observable';

@Injectable({
  providedIn: 'root'
})


export class ApiService {

  url: string = 'http://localhost/iform/backendAPI/';

  constructor(private _http: HttpClient) { }

  // API to store form detail
  create(data: any): Observable<any> {
    return this._http.post(this.url, data);
  }

}

