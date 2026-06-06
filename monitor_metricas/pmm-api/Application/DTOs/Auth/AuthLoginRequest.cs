namespace ProjectMetricsMonitor.Application.DTOs.Auth;

public sealed record AuthLoginRequest(
    string Email,
    string Password
);

