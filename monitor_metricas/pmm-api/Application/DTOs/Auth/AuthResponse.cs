namespace ProjectMetricsMonitor.Application.DTOs.Auth;

public sealed record AuthResponse(
    string AccessToken,
    UserDto User,
    DateTimeOffset Expiration
);

