using FluentValidation;
using ProjectMetricsMonitor.Application.DTOs.Auth;

namespace ProjectMetricsMonitor.Application.Validators.Auth;

public sealed class ForgotPasswordRequestValidator : AbstractValidator<ForgotPasswordRequest>
{
    public ForgotPasswordRequestValidator()
    {
        RuleFor(x => x.Email).NotEmpty().EmailAddress();
    }
}

