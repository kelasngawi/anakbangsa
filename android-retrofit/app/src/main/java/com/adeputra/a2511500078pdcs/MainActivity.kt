package com.adeputra.a2511500078pdcs

import android.os.Bundle
import android.graphics.Color
import android.view.View
import androidx.activity.ComponentActivity
import androidx.lifecycle.lifecycleScope
import com.adeputra.a2511500078pdcs.api.ApiConfig
import com.adeputra.a2511500078pdcs.databinding.ActivityMainBinding
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import java.io.IOException
import java.net.SocketTimeoutException

class MainActivity : ComponentActivity() {

    private lateinit var binding: ActivityMainBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        binding.btnTestConnection.setOnClickListener {
            testConnection()
        }
    }

    private fun testConnection() {
        binding.progressBar.visibility = View.VISIBLE
        binding.tvConnectionStatus.text = "Status: Menghubungkan..."
        binding.tvConnectionStatus.setTextColor(Color.BLACK)

        lifecycleScope.launch {
            try {
                val response = withContext(Dispatchers.IO) {
                    ApiConfig.getApiService().getDaftarMahasiswa()
                }

                if (response.isSuccessful && response.body()?.status == true) {
                    binding.tvConnectionStatus.text =
                        "Status: Berhasil terhubung ke API!"
                    binding.tvConnectionStatus.setTextColor(Color.GREEN)
                } else {
                    binding.tvConnectionStatus.text =
                        "Status: Kesalahan HTTP ${response.code()} - ${response.message()}"
                    binding.tvConnectionStatus.setTextColor(Color.RED)
                }

            } catch (e: SocketTimeoutException) {
                binding.tvConnectionStatus.text =
                    "Status: Timeout, server terlalu lama merespons"
                binding.tvConnectionStatus.setTextColor(Color.RED)

            } catch (e: HttpException) {
                binding.tvConnectionStatus.text =
                    "Status: Kesalahan HTTP - ${e.message}"
                binding.tvConnectionStatus.setTextColor(Color.RED)

            } catch (e: IOException) {
                binding.tvConnectionStatus.text =
                    "Status: Gagal terhubung, periksa IP atau server"
                binding.tvConnectionStatus.setTextColor(Color.RED)

            } catch (e: Exception) {
                binding.tvConnectionStatus.text =
                    "Status: Terjadi kesalahan - ${e.message}"
                binding.tvConnectionStatus.setTextColor(Color.RED)

            } finally {
                binding.progressBar.visibility = View.GONE
            }
        }
    }
}