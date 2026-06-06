using ProjectMetricsMonitor.Domain.Entities;

namespace ProjectMetricsMonitor.Application.Interfaces;

public interface IJwtTokenService
{
    (string Token, DateTimeOffset ExpiresAt) CreateToken(User user);
}

