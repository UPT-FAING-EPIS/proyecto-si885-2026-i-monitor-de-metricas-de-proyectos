namespace ProjectMetricsMonitor.Application.DTOs.Auth;

public sealed record AuthRegisterRequest(
    string FullName,
    string Email,
    string Password
);

