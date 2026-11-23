package id.sch.maknende.absensi;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.net.http.SslError;
import android.os.Build;
import android.os.Bundle;
import android.text.InputType;
import android.view.ViewGroup;
import android.webkit.JavascriptInterface;
import android.webkit.PermissionRequest;
import android.webkit.SslErrorHandler;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.EditText;
import android.widget.FrameLayout;

/**
 * Pembungkus WebView untuk E-Absensi MAKN Ende. Alamat server bisa diganti
 * langsung dari HP (disimpan di SharedPreferences), sehingga pergantian
 * domain/tunnel tidak perlu build ulang APK.
 */
public class MainActivity extends Activity {

    private static final int REQ_CAMERA = 10;
    private static final String PREFS = "eabsensi";
    private static final String KEY_SERVER = "server_url";
    private static final String APP_VERSION = "1.0.2";

    private WebView webView;
    private String serverUrl;
    private SharedPreferences prefs;

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        prefs = getSharedPreferences(PREFS, MODE_PRIVATE);
        serverUrl = normalize(prefs.getString(KEY_SERVER, getString(R.string.server_url)));

        webView = new WebView(this);
        setContentView(webView);

        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setUseWideViewPort(true);
        settings.setLoadWithOverviewMode(true);
        settings.setSupportZoom(false);
        settings.setBuiltInZoomControls(false);
        settings.setDisplayZoomControls(false);
        settings.setMediaPlaybackRequiresUserGesture(false);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE);
        settings.setUserAgentString(settings.getUserAgentString() + " EabsensiApp/" + APP_VERSION);

        webView.addJavascriptInterface(new Bridge(), "EabsensiApp");
        webView.setOverScrollMode(WebView.OVER_SCROLL_NEVER);

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                Uri uri = request.getUrl();
                String appHost = Uri.parse(serverUrl).getHost();
                if (uri.getHost() != null && uri.getHost().equals(appHost)) {
                    return false;
                }
                try {
                    startActivity(new Intent(Intent.ACTION_VIEW, uri));
                } catch (Exception ignored) {
                    // tidak ada aplikasi pembuka tautan
                }
                return true;
            }

            @Override
            public void onReceivedSslError(WebView view, SslErrorHandler handler, SslError error) {
                // Sertifikat lokal (self-signed) hanya diterima untuk host aplikasi sendiri.
                String host = error.getUrl() == null ? null : Uri.parse(error.getUrl()).getHost();
                String appHost = Uri.parse(serverUrl).getHost();
                if (host != null && host.equals(appHost)) {
                    handler.proceed();
                } else {
                    handler.cancel();
                }
            }

            @Override
            public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                if (request.isForMainFrame()) {
                    view.loadUrl("file:///android_asset/offline.html");
                }
            }
        });

        webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onPermissionRequest(final PermissionRequest request) {
                runOnUiThread(() -> request.grant(request.getResources()));
            }
        });

        requestCameraIfNeeded();
        webView.loadUrl(serverUrl);
    }

    /**
     * Rapikan alamat: tambah https:// bila belum ada skema, buang spasi & garis
     * miring di akhir.
     */
    private static String normalize(String raw) {
        String url = raw == null ? "" : raw.trim();
        if (url.isEmpty()) {
            return "";
        }
        if (!url.matches("(?i)^https?://.*")) {
            url = (url.startsWith("localhost") || url.startsWith("127.0.0.1") || url.matches("^\\d+(\\.\\d+){3}.*"))
                    ? "http://" + url
                    : "https://" + url;
        }
        while (url.endsWith("/")) {
            url = url.substring(0, url.length() - 1);
        }
        return url;
    }

    private void applyServer(String raw) {
        String url = normalize(raw);
        if (url.isEmpty() || Uri.parse(url).getHost() == null) {
            toast("Alamat tidak valid. Contoh: https://absensi.maknende.my.id");
            return;
        }
        serverUrl = url;
        prefs.edit().putString(KEY_SERVER, url).apply();
        if (webView != null) {
            webView.loadUrl(url);
        }
    }

    private void toast(String message) {
        android.widget.Toast.makeText(this, message, android.widget.Toast.LENGTH_LONG).show();
    }

    /**
     * Dialog pengaturan alamat server.
     */
    private void openServerSettings() {
        final EditText input = new EditText(this);
        input.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_URI);
        input.setText(serverUrl);
        input.setSelectAllOnFocus(true);
        input.setSingleLine(true);

        FrameLayout wrapper = new FrameLayout(this);
        int pad = Math.round(20 * getResources().getDisplayMetrics().density);
        wrapper.setPadding(pad, pad / 2, pad, 0);
        wrapper.addView(input, new FrameLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT));

        new AlertDialog.Builder(this)
                .setTitle("Alamat server")
                .setMessage("Masukkan alamat server E-Absensi. Contoh:\nhttps://absensi.maknende.my.id\nhttp://192.168.2.10:8000")
                .setView(wrapper)
                .setPositiveButton("Simpan", (dialog, which) -> applyServer(input.getText().toString()))
                .setNeutralButton("Bawaan", (dialog, which) -> applyServer(getString(R.string.server_url)))
                .setNegativeButton("Batal", null)
                .show();
    }

    private void requestCameraIfNeeded() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M
                && checkSelfPermission(android.Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED) {
            requestPermissions(new String[]{android.Manifest.permission.CAMERA}, REQ_CAMERA);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == REQ_CAMERA && webView != null) {
            webView.reload();
        }
    }

    @Override
    public void onBackPressed() {
        if (webView != null && webView.canGoBack()) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }

    @Override
    protected void onDestroy() {
        if (webView != null) {
            webView.destroy();
            webView = null;
        }
        super.onDestroy();
    }

    /**
     * Jembatan untuk halaman offline dan halaman login.
     */
    private class Bridge {

        @JavascriptInterface
        public void reload() {
            runOnUiThread(() -> {
                if (webView != null) {
                    webView.loadUrl(serverUrl);
                }
            });
        }

        @JavascriptInterface
        public void openSettings() {
            runOnUiThread(MainActivity.this::openServerSettings);
        }

        @JavascriptInterface
        public String getServerUrl() {
            return serverUrl;
        }

        @JavascriptInterface
        public String getVersion() {
            return APP_VERSION;
        }
    }
}
