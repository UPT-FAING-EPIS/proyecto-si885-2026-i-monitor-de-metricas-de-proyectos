namespace ProjectMetricsMonitor.Application.Models;

public sealed record SupabaseUserInfo(
    string SupabaseId,
    string Email,
    string? FullName,
    string Provider
);

