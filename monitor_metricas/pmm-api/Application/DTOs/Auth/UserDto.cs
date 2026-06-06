namespace ProjectMetricsMonitor.Application.DTOs.Auth;

public sealed record UserDto(
    Guid Id,
    string FullName,
    string Email
);

