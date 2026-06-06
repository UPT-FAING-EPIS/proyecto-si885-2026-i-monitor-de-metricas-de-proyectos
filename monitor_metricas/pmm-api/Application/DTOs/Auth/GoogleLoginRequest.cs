namespace ProjectMetricsMonitor.Application.DTOs.Auth;

public sealed record GoogleLoginRequest(
    string SupabaseAccessToken
);

