using System.Net.Http.Headers;
using System.Net.Http.Json;
using System.Text.Json;
using System.Text.Json.Serialization;
using Microsoft.Extensions.Options;
using ProjectMetricsMonitor.Application.Exceptions;
using ProjectMetricsMonitor.Application.Interfaces;
using ProjectMetricsMonitor.Application.Models;
using ProjectMetricsMonitor.Infrastructure.Options;

namespace ProjectMetricsMonitor.Infrastructure.Services;

public sealed class SupabaseAuthService : ISupabaseAuthService
{
    private readonly HttpClient _http;
    private readonly SupabaseOptions _options;

    public SupabaseAuthService(HttpClient http, IOptions<SupabaseOptions> options)
    {
        _http = http;
        _options = options.Value;
    }

    public async Task<SupabaseUserInfo> RegisterAsync(string email, string password, CancellationToken cancellationToken)
    {
        using var request = new HttpRequestMessage(HttpMethod.Post, $"{_options.Url.TrimEnd('/')}/auth/v1/signup");
        request.Headers.Add("apikey", _options.AnonKey);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", _options.AnonKey);
        request.Content = JsonContent.Create(new { email, password });

        using var response = await _http.SendAsync(request, cancellationToken);
        await EnsureSuccessOrThrowAsync(response, "Registro fallido", cancellationToken);

        var payload = await response.Content.ReadFromJsonAsync<AuthSessionResponse>(cancellationToken: cancellationToken);
        var user = payload?.User ?? throw new InvalidOperationException("Supabase response missing user.");

        return MapUser(user, "email");
    }

    public async Task<SupabaseUserInfo> LoginWithPasswordAsync(string email, string password, CancellationToken cancellationToken)
    {
        using var request = new HttpRequestMessage(
            HttpMethod.Post,
            $"{_options.Url.TrimEnd('/')}/auth/v1/token?grant_type=password"
        );
        request.Headers.Add("apikey", _options.AnonKey);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", _options.AnonKey);
        request.Content = JsonContent.Create(new { email, password });

        using var response = await _http.SendAsync(request, cancellationToken);
        await EnsureSuccessOrThrowAsync(response, "Login fallido", cancellationToken);

        var payload = await response.Content.ReadFromJsonAsync<AuthSessionResponse>(cancellationToken: cancellationToken);
        var user = payload?.User ?? throw new InvalidOperationException("Supabase response missing user.");

        return MapUser(user, "email");
    }

    public async Task<SupabaseUserInfo> GetUserAsync(string supabaseAccessToken, CancellationToken cancellationToken)
    {
        using var request = new HttpRequestMessage(HttpMethod.Get, $"{_options.Url.TrimEnd('/')}/auth/v1/user");
        request.Headers.Add("apikey", _options.AnonKey);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", supabaseAccessToken);

        using var response = await _http.SendAsync(request, cancellationToken);
        await EnsureSuccessOrThrowAsync(response, "Token de Supabase inválido", cancellationToken);

        var user = await response.Content.ReadFromJsonAsync<SupabaseUserResponse>(cancellationToken: cancellationToken)
                   ?? throw new InvalidOperationException("Supabase response missing user.");

        var provider = user.AppMetadata?.Provider ?? "google";
        return MapUser(user, provider);
    }

    public async Task SendPasswordResetAsync(string email, CancellationToken cancellationToken)
    {
        using var request = new HttpRequestMessage(HttpMethod.Post, $"{_options.Url.TrimEnd('/')}/auth/v1/recover");
        request.Headers.Add("apikey", _options.AnonKey);
        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", _options.AnonKey);
        request.Content = JsonContent.Create(new { email });

        using var response = await _http.SendAsync(request, cancellationToken);
        await EnsureSuccessOrThrowAsync(response, "No se pudo enviar el enlace", cancellationToken);
    }

    private static async Task EnsureSuccessOrThrowAsync(
        HttpResponseMessage response,
        string title,
        CancellationToken cancellationToken
    )
    {
        if (response.IsSuccessStatusCode) return;

        var statusCode = (int)response.StatusCode;
        var raw = await response.Content.ReadAsStringAsync(cancellationToken);

        SupabaseErrorPayload? payload = null;
        try
        {
            payload = JsonSerializer.Deserialize<SupabaseErrorPayload>(raw);
        }
        catch
        {
        }

        var detail =
            payload?.ErrorDescription
            ?? payload?.Message
            ?? payload?.Msg
            ?? payload?.Error
            ?? (string.IsNullOrWhiteSpace(raw) ? null : raw);

        if (!string.IsNullOrWhiteSpace(payload?.Error) &&
            payload.Error.Equals("invalid_grant", StringComparison.OrdinalIgnoreCase))
        {
            statusCode = 401;
        }

        if (!string.IsNullOrWhiteSpace(detail) &&
            (detail.Contains("Invalid login credentials", StringComparison.OrdinalIgnoreCase) ||
             detail.Contains("invalid login credentials", StringComparison.OrdinalIgnoreCase)))
        {
            statusCode = 401;
        }

        if (!string.IsNullOrWhiteSpace(detail) &&
            (detail.Contains("User already registered", StringComparison.OrdinalIgnoreCase) ||
             detail.Contains("already registered", StringComparison.OrdinalIgnoreCase)))
        {
            statusCode = 409;
        }

        throw new HttpStatusCodeException(statusCode, title, string.IsNullOrWhiteSpace(detail) ? null : detail);
    }

    private static SupabaseUserInfo MapUser(SupabaseUserResponse user, string provider)
    {
        var fullName =
            user.UserMetadata?.FullName
            ?? user.UserMetadata?.Name
            ?? user.Identities?.FirstOrDefault()?.IdentityData?.FullName
            ?? user.Identities?.FirstOrDefault()?.IdentityData?.Name;

        return new SupabaseUserInfo(
            user.Id ?? string.Empty,
            user.Email ?? string.Empty,
            fullName,
            provider
        );
    }

    private sealed class AuthSessionResponse
    {
        [JsonPropertyName("user")]
        public SupabaseUserResponse? User { get; set; }
    }

    private sealed class SupabaseUserResponse
    {
        [JsonPropertyName("id")]
        public string? Id { get; set; }

        [JsonPropertyName("email")]
        public string? Email { get; set; }

        [JsonPropertyName("app_metadata")]
        public AppMetadata? AppMetadata { get; set; }

        [JsonPropertyName("user_metadata")]
        public UserMetadata? UserMetadata { get; set; }

        [JsonPropertyName("identities")]
        public List<Identity>? Identities { get; set; }
    }

    private sealed class AppMetadata
    {
        [JsonPropertyName("provider")]
        public string? Provider { get; set; }
    }

    private sealed class UserMetadata
    {
        [JsonPropertyName("full_name")]
        public string? FullName { get; set; }

        [JsonPropertyName("name")]
        public string? Name { get; set; }
    }

    private sealed class Identity
    {
        [JsonPropertyName("provider")]
        public string? Provider { get; set; }

        [JsonPropertyName("identity_data")]
        public IdentityData? IdentityData { get; set; }
    }

    private sealed class IdentityData
    {
        [JsonPropertyName("full_name")]
        public string? FullName { get; set; }

        [JsonPropertyName("name")]
        public string? Name { get; set; }
    }

    private sealed class SupabaseErrorPayload
    {
        [JsonPropertyName("msg")]
        public string? Msg { get; set; }

        [JsonPropertyName("message")]
        public string? Message { get; set; }

        [JsonPropertyName("error_description")]
        public string? ErrorDescription { get; set; }

        [JsonPropertyName("error")]
        public string? Error { get; set; }
    }
}
