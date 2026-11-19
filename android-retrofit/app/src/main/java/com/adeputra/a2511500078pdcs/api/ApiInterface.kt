package com.adeputra.a2511500078pdcs.api

import com.adeputra.a2511500078pdcs.model.MahasiswaResponse
import retrofit2.Response
import retrofit2.http.GET

interface ApiInterface {

    @GET("mahasiswa.php")
    suspend fun getDaftarMahasiswa(): Response<MahasiswaResponse>

}